import React, { useState, useEffect } from "react";
import { collection, query, orderBy, getDocs, addDoc, doc, getDoc } from "firebase/firestore";
import { db } from "../lib/firebase";
import { Customer, Invoice, InvoiceItem, BusinessSettings } from "../types";
import { formatCurrency, cn } from "../lib/utils";
import { 
  Users, 
  Trash2, 
  Plus, 
  Save, 
  Loader2, 
  ChevronLeft, 
  FileText, 
  UserPlus
} from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import { format } from "date-fns";

export function NewInvoice() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [settings, setSettings] = useState<BusinessSettings | null>(null);
  
  const [selectedCustomerId, setSelectedCustomerId] = useState("");
  const [invoiceNumber, setInvoiceNumber] = useState(`INV-${Date.now().toString().slice(-6)}`);
  const [items, setItems] = useState<InvoiceItem[]>([{ description: "", quantity: 1, price: 0 }]);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    async function fetchData() {
      try {
        const customersSnap = await getDocs(query(collection(db, "customers"), orderBy("name")));
        const cList: Customer[] = [];
        customersSnap.forEach(doc => cList.push({ ...doc.data() as Customer, id: doc.id }));
        setCustomers(cList);

        const settingsSnap = await getDoc(doc(db, "settings", "business"));
        if (settingsSnap.exists()) {
          setSettings(settingsSnap.data() as BusinessSettings);
        }
      } catch (error) {
        console.error("Error fetching data:", error);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, []);

  const addItem = () => setItems([...items, { description: "", quantity: 1, price: 0 }]);
  const removeItem = (index: number) => setItems(items.filter((_, i) => i !== index));
  const updateItem = (index: number, field: keyof InvoiceItem, value: any) => {
    const newItems = [...items];
    (newItems[index] as any)[field] = value;
    setItems(newItems);
  };

  const subtotal = items.reduce((acc, item) => acc + (item.quantity * item.price), 0);
  const tax = subtotal * 0.1; // Example 10% tax
  const total = subtotal + tax;

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedCustomerId) return alert("Please select a customer");
    if (items.some(i => !i.description || i.price <= 0)) return alert("Please fill in all item details");

    setSaving(true);
    try {
      const customer = customers.find(c => c.id === selectedCustomerId);
      const invoiceData: Omit<Invoice, 'id'> = {
        invoiceNumber,
        customerId: selectedCustomerId,
        customerName: customer?.name || "Unknown",
        items,
        subtotal,
        tax,
        total,
        date: new Date().toISOString(),
        status: 'pending'
      };
      
      const docRef = await addDoc(collection(db, "invoices"), invoiceData);
      navigate(`/invoices/${docRef.id}`);
    } catch (error) {
      console.error("Error saving invoice:", error);
      alert("Failed to save invoice.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center py-20"><Loader2 className="w-8 h-8 text-blue-500 animate-spin" /></div>;
  }

  return (
    <div className="space-y-8 pb-20">
      <div className="flex items-center gap-4">
        <Link to="/invoices" className="p-2 hover:bg-gray-100 rounded-xl transition-colors">
          <ChevronLeft className="w-6 h-6" />
        </Link>
        <div>
          <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Create New Invoice</h1>
          <p className="text-gray-500 mt-1">Issue a new billing statement for your ecommerce customer.</p>
        </div>
      </div>

      <form onSubmit={handleSave} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          {/* Customer Selection */}
          <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h2 className="text-lg font-bold flex items-center gap-2">
              <Users className="w-5 h-5 text-blue-500" />
              Customer Information
            </h2>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Select Customer</label>
                <div className="flex gap-2">
                  <select
                    required
                    value={selectedCustomerId}
                    onChange={(e) => setSelectedCustomerId(e.target.value)}
                    className="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                  >
                    <option value="">-- Choose a customer --</option>
                    {customers.map(c => (
                      <option key={c.id} value={c.id}>{c.name} ({c.email || 'No email'})</option>
                    ))}
                  </select>
                  <Link to="/customers" className="p-2 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 transition-colors border border-gray-100">
                    <UserPlus className="w-6 h-6" />
                  </Link>
                </div>
              </div>

              {selectedCustomerId && (
                <div className="p-4 bg-blue-50/50 rounded-xl border border-blue-100 text-sm text-blue-800 grid grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-1">
                  <div>
                    <span className="block font-bold">Billing Address</span>
                    {customers.find(c => c.id === selectedCustomerId)?.address || 'No address provided'}
                  </div>
                  <div>
                    <span className="block font-bold">Contact</span>
                    {customers.find(c => c.id === selectedCustomerId)?.email}
                    <br />
                    {customers.find(c => c.id === selectedCustomerId)?.phone}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Items Section */}
          <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div className="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 className="text-lg font-bold flex items-center gap-2">
                <FileText className="w-5 h-5 text-blue-500" />
                Line Items
              </h2>
              <button
                type="button"
                onClick={addItem}
                className="text-blue-600 text-sm font-bold flex items-center gap-1 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-all"
              >
                <Plus className="w-4 h-4" /> Add Item
              </button>
            </div>
            
            <div className="p-6">
              <table className="w-full">
                <thead className="text-left text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 block">
                  <tr className="flex gap-4 px-2">
                    <th className="flex-1">Description</th>
                    <th className="w-24">Qty</th>
                    <th className="w-32">Price</th>
                    <th className="w-32 text-right">Total</th>
                    <th className="w-10"></th>
                  </tr>
                </thead>
                <tbody className="space-y-4 block">
                  {items.map((item, index) => (
                    <tr key={index} className="flex gap-4 group">
                      <td className="flex-1">
                        <input
                          type="text"
                          required
                          placeholder="Product Name / Description"
                          value={item.description}
                          onChange={(e) => updateItem(index, 'description', e.target.value)}
                          className="w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                        />
                      </td>
                      <td className="w-24">
                        <input
                          type="number"
                          min="1"
                          value={item.quantity}
                          onChange={(e) => updateItem(index, 'quantity', parseInt(e.target.value) || 0)}
                          className="w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                        />
                      </td>
                      <td className="w-32">
                        <div className="relative">
                          <input
                            type="number"
                            min="0"
                            step="0.01"
                            value={item.price}
                            onChange={(e) => updateItem(index, 'price', parseFloat(e.target.value) || 0)}
                            className="w-full px-4 py-2 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </td>
                      <td className="w-32 py-2 text-right font-bold text-gray-700">
                        {formatCurrency(item.quantity * item.price, settings?.currency)}
                      </td>
                      <td className="w-10 relative">
                        <button
                          type="button"
                          disabled={items.length === 1}
                          onClick={() => removeItem(index)}
                          className="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-0"
                        >
                          <Trash2 className="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {/* Sidebar Summary */}
        <div className="space-y-6">
          <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h3 className="text-lg font-bold">Invoice Details</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                <input
                  type="text"
                  value={invoiceNumber}
                  onChange={(e) => setInvoiceNumber(e.target.value)}
                  className="w-full px-4 py-2 border border-gray-100 rounded-xl font-bold text-blue-600 bg-blue-50/30"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <div className="w-full px-4 py-2 border border-gray-100 rounded-xl text-gray-500 bg-gray-50">
                  {format(new Date(), "MMMM d, yyyy")}
                </div>
              </div>
            </div>

            <div className="pt-6 border-t border-gray-100 space-y-3">
              <div className="flex justify-between text-gray-500">
                <span>Subtotal</span>
                <span>{formatCurrency(subtotal, settings?.currency)}</span>
              </div>
              <div className="flex justify-between text-gray-500">
                <span>Tax (10%)</span>
                <span>{formatCurrency(tax, settings?.currency)}</span>
              </div>
              <div className="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t border-gray-100">
                <span>Total</span>
                <span>{formatCurrency(total, settings?.currency)}</span>
              </div>
            </div>

            <button
              type="submit"
              disabled={saving}
              className="w-full flex items-center justify-center gap-2 py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 disabled:opacity-50"
            >
              {saving ? <Loader2 className="w-6 h-6 animate-spin" /> : <Save className="w-6 h-6" />}
              Save Invoice
            </button>
          </div>

          <div className="bg-amber-50 p-6 rounded-2xl border border-amber-100">
            <h4 className="font-bold text-amber-800 mb-1 flex items-center gap-2">
              <Calculation className="w-4 h-4" />
              Pro-Tip
            </h4>
            <p className="text-xs text-amber-700 leading-relaxed">
              Double check quantities and prices. After saving, the status will be set to 'Pending' by default. You can change this later from the invoices list.
            </p>
          </div>
        </div>
      </form>
    </div>
  );
}

function Calculation({ className }: { className?: string }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
      <path d="M12 2v20"/><path d="M5 12h14"/><path d="m15 5 2 2 2-2"/><path d="m15 19 2-2 2 2"/>
    </svg>
  );
}
