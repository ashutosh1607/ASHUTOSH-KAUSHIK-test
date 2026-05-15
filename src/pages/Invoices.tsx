import { useState, useEffect } from "react";
import { collection, query, orderBy, getDocs, deleteDoc, doc, updateDoc } from "firebase/firestore";
import { db } from "../lib/firebase";
import { Invoice } from "../types";
import { formatCurrency } from "../lib/utils";
import { FileText, Plus, Search, Filter, Trash2, ExternalLink, Loader2, MoreVertical } from "lucide-react";
import { Link } from "react-router-dom";
import { format } from "date-fns";
import { motion, AnimatePresence } from "motion/react";

export function Invoices() {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");

  useEffect(() => {
    fetchInvoices();
  }, []);

  async function fetchInvoices() {
    try {
      const q = query(collection(db, "invoices"), orderBy("date", "desc"));
      const querySnapshot = await getDocs(q);
      const data: Invoice[] = [];
      querySnapshot.forEach((doc) => {
        data.push({ ...doc.data() as Invoice, id: doc.id });
      });
      setInvoices(data);
    } catch (error) {
      console.error("Error fetching invoices:", error);
    } finally {
      setLoading(false);
    }
  }

  const handleDelete = async (id: string) => {
    if (window.confirm("Are you sure you want to delete this invoice?")) {
      try {
        await deleteDoc(doc(db, "invoices", id));
        setInvoices(invoices.filter(i => i.id !== id));
      } catch (error) {
        console.error("Error deleting invoice:", error);
      }
    }
  };

  const handleStatusChange = async (id: string, newStatus: string) => {
    try {
      await updateDoc(doc(db, "invoices", id), { status: newStatus });
      setInvoices(invoices.map(i => i.id === id ? { ...i, status: newStatus as any } : i));
    } catch (error) {
      console.error("Error updating status:", error);
    }
  };

  const filteredInvoices = invoices.filter(i => {
    const matchesSearch = i.invoiceNumber.toLowerCase().includes(searchTerm.toLowerCase()) || 
                         i.customerName.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === 'all' || i.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  if (loading) {
    return <div className="flex justify-center py-20"><Loader2 className="w-8 h-8 text-blue-500 animate-spin" /></div>;
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Invoices</h1>
          <p className="text-gray-500 mt-1">Manage and track your customer invoices.</p>
        </div>
        <Link
          to="/invoices/new"
          className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100"
        >
          <Plus className="w-5 h-5" />
          Create Invoice
        </Link>
      </div>

      {/* Filters */}
      <div className="flex flex-col md:flex-row gap-4">
        <div className="flex-1 relative">
          <input
            type="text"
            placeholder="Search by ID or customer..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium"
          />
          <Search className="absolute left-4 top-3.5 w-5 h-5 text-gray-400" />
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all font-medium min-w-[150px]"
        >
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      {/* Invoice List */}
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            <tr>
              <th className="px-6 py-4">Number</th>
              <th className="px-6 py-4">Customer</th>
              <th className="px-6 py-4">Date</th>
              <th className="px-6 py-4 text-right">Amount</th>
              <th className="px-6 py-4">Status</th>
              <th className="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {filteredInvoices.map((invoice) => (
              <motion.tr 
                layout
                key={invoice.id} 
                className="hover:bg-gray-50 transition-colors group"
              >
                <td className="px-6 py-4">
                  <Link to={`/invoices/${invoice.id}`} className="font-bold text-gray-900 hover:text-blue-600 transition-colors uppercase">
                    #{invoice.invoiceNumber}
                  </Link>
                </td>
                <td className="px-6 py-4">
                  <div className="flex flex-col">
                    <span className="text-gray-900 font-medium">{invoice.customerName}</span>
                    <span className="text-xs text-gray-400">ID: {invoice.customerId.slice(-6)}</span>
                  </div>
                </td>
                <td className="px-6 py-4 text-gray-500">{format(new Date(invoice.date), "MMM d, yyyy")}</td>
                <td className="px-6 py-4 text-right font-bold text-gray-900">{formatCurrency(invoice.total)}</td>
                <td className="px-6 py-4">
                  <select
                    value={invoice.status}
                    onChange={(e) => handleStatusChange(invoice.id!, e.target.value)}
                    className={`text-xs font-bold px-3 py-1 rounded-full border-none cursor-pointer focus:ring-0 ${
                      invoice.status === 'paid' ? 'bg-green-100 text-green-700' : 
                      invoice.status === 'pending' ? 'bg-amber-100 text-amber-700' : 
                      'bg-red-100 text-red-700'
                    }`}
                  >
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </td>
                <td className="px-6 py-4 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <Link 
                      to={`/invoices/${invoice.id}`} 
                      className="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded-lg hover:bg-blue-50"
                      title="View Detail"
                    >
                      <ExternalLink className="w-5 h-5" />
                    </Link>
                    <button 
                      onClick={() => handleDelete(invoice.id!)}
                      className="p-2 text-gray-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50"
                      title="Delete"
                    >
                      <Trash2 className="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </motion.tr>
            ))}
            {filteredInvoices.length === 0 && (
              <tr>
                <td colSpan={6} className="px-6 py-20 text-center text-gray-500">
                  <div className="flex flex-col items-center">
                    <FileText className="w-12 h-12 text-gray-200 mb-4" />
                    <p className="text-lg font-medium text-gray-400">No invoices found.</p>
                  </div>
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
