import { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import { doc, getDoc } from "firebase/firestore";
import { db } from "../lib/firebase";
import { Invoice, BusinessSettings, Customer } from "../types";
import { formatCurrency, cn } from "../lib/utils";
import { 
  Printer, 
  ChevronLeft, 
  Download, 
  Mail, 
  CreditCard,
  Loader2,
  Calendar,
  User as UserIcon,
  CheckCircle2,
  Clock,
  Ban
} from "lucide-react";
import { format } from "date-fns";

export function InvoiceDetail() {
  const { id } = useParams();
  const [invoice, setInvoice] = useState<Invoice | null>(null);
  const [settings, setSettings] = useState<BusinessSettings | null>(null);
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      if (!id) return;
      try {
        const invoiceSnap = await getDoc(doc(db, "invoices", id));
        if (invoiceSnap.exists()) {
          const invData = invoiceSnap.data() as Invoice;
          setInvoice(invData);
          
          const [settingsSnap, customerSnap] = await Promise.all([
            getDoc(doc(db, "settings", "business")),
            getDoc(doc(db, "customers", invData.customerId))
          ]);

          if (settingsSnap.exists()) setSettings(settingsSnap.data() as BusinessSettings);
          if (customerSnap.exists()) setCustomer(customerSnap.data() as Customer);
        }
      } catch (error) {
        console.error("Error fetching invoice:", error);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [id]);

  const handlePrint = () => {
    window.print();
  };

  if (loading) {
    return <div className="flex justify-center py-20"><Loader2 className="w-8 h-8 text-blue-500 animate-spin" /></div>;
  }

  if (!invoice) {
    return <div className="text-center py-20">Invoice not found.</div>;
  }

  const statusIcons = {
    paid: { icon: CheckCircle2, color: 'text-green-600', bg: 'bg-green-50' },
    pending: { icon: Clock, color: 'text-amber-600', bg: 'bg-amber-50' },
    cancelled: { icon: Ban, color: 'text-red-600', bg: 'bg-red-50' }
  };

  const StatusIcon = statusIcons[invoice.status].icon;

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Header Actions - Hidden on Print */}
      <div className="print:hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <Link to="/invoices" className="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-gray-200">
            <ChevronLeft className="w-6 h-6" />
          </Link>
          <div>
            <div className="flex items-center gap-3">
              <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Invoice #{invoice.invoiceNumber}</h1>
              <span className={cn(
                "px-3 py-1 rounded-full text-xs font-bold uppercase",
                statusIcons[invoice.status].bg,
                statusIcons[invoice.status].color
              )}>
                {invoice.status}
              </span>
            </div>
            <p className="text-gray-500 mt-1">Generated on {format(new Date(invoice.date), "MMMM d, yyyy")}</p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={handlePrint}
            className="flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all shadow-sm"
          >
            <Printer className="w-5 h-5" />
            Print Invoice
          </button>
          <button
            className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100"
          >
            <Download className="w-5 h-5" />
            Download PDF
          </button>
        </div>
      </div>

      {/* Invoice Document */}
      <div id="invoice-bill" className="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden print:shadow-none print:border-none print:m-0 print:p-0">
        {/* Branding & Info */}
        <div className="p-12 md:p-16 space-y-12">
          <div className="flex flex-col md:flex-row justify-between gap-12">
            <div className="space-y-6">
              {settings?.logoUrl ? (
                <img src={settings.logoUrl} alt="Company Logo" className="h-16 object-contain" />
              ) : (
                <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-3xl">
                  {settings?.adminName?.charAt(0) || 'I'}
                </div>
              )}
              <div className="space-y-1">
                <h2 className="text-xl font-bold text-gray-900">{settings?.adminName || 'Your Business Name'}</h2>
                <div className="text-gray-500 text-sm whitespace-pre-wrap leading-relaxed max-w-xs transition-all">
                  {settings?.businessAddress || 'Set your business address in settings'}
                </div>
                <div className="flex flex-col gap-1 pt-2 text-sm text-gray-400">
                  {settings?.email && <span className="flex items-center gap-2"><Mail className="w-3.5 h-3.5" /> {settings.email}</span>}
                  {settings?.phone && <span className="flex items-center gap-2"><CreditCard className="w-3.5 h-3.5" /> {settings.phone}</span>}
                </div>
              </div>
            </div>

            <div className="text-right space-y-6">
              <div className="space-y-1">
                <h1 className="text-5xl font-black text-gray-900 uppercase tracking-tighter">Invoice</h1>
                <p className="text-blue-600 font-bold text-xl uppercase tracking-widest">#{invoice.invoiceNumber}</p>
              </div>
              <div className="grid grid-cols-2 gap-4 text-sm font-medium">
                <div className="text-left py-2 px-4 bg-gray-50 rounded-lg">
                  <span className="block text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Issue Date</span>
                  <span className="text-gray-900">{format(new Date(invoice.date), "MMM d, yyyy")}</span>
                </div>
                <div className="text-left py-2 px-4 bg-gray-50 rounded-lg">
                  <span className="block text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Status</span>
                  <span className={cn("flex items-center gap-1.5 font-bold uppercase", statusIcons[invoice.status].color)}>
                    <StatusIcon className="w-3.5 h-3.5" />
                    {invoice.status}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div className="h-px bg-gray-100" />

          {/* Billing Sections */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div className="space-y-4">
              <h3 className="text-[10px] uppercase font-black tracking-[0.2em] text-gray-400">Billed To</h3>
              <div className="space-y-1">
                <p className="text-2xl font-black text-gray-900 uppercase">{customer?.name}</p>
                <div className="text-gray-500 text-sm whitespace-pre-wrap leading-relaxed max-w-xs italic">
                  {customer?.address || 'No address provided'}
                </div>
                <div className="flex flex-col gap-1 pt-4 text-sm text-gray-400 font-bold">
                   <span className="flex items-center gap-2 uppercase tracking-wider">{customer?.email}</span>
                   <span className="flex items-center gap-2 uppercase tracking-wider">{customer?.phone}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Table */}
          <div className="overflow-hidden rounded-2xl border border-gray-100">
            <table className="w-full text-left">
              <thead className="bg-gray-900 text-[10px] uppercase font-black text-white tracking-[0.2em]">
                <tr>
                  <th className="px-8 py-5">S.No</th>
                  <th className="px-8 py-5">Description</th>
                  <th className="px-8 py-5 text-center">Qty</th>
                  <th className="px-8 py-5 text-right">Price</th>
                  <th className="px-8 py-5 text-right">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {invoice.items.map((item, index) => (
                  <tr key={index} className="hover:bg-gray-50/50 transition-colors">
                    <td className="px-8 py-5 text-gray-400 font-mono text-sm">{(index + 1).toString().padStart(2, '0')}</td>
                    <td className="px-8 py-5 font-bold text-gray-900">{item.description}</td>
                    <td className="px-8 py-5 text-center font-medium text-gray-600">{item.quantity}</td>
                    <td className="px-8 py-5 text-right font-medium text-gray-600">{formatCurrency(item.price, settings?.currency)}</td>
                    <td className="px-8 py-5 text-right font-black text-gray-900">{formatCurrency(item.quantity * item.price, settings?.currency)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Footer Totals */}
          <div className="flex flex-col md:flex-row justify-end text-right gap-12">
            <div className="w-full md:w-80 space-y-4">
              <div className="space-y-3 py-6 px-1 border-t-2 border-gray-900 font-medium">
                <div className="flex justify-between text-gray-500 uppercase tracking-widest text-xs">
                  <span>Subtotal</span>
                  <span>{formatCurrency(invoice.subtotal, settings?.currency)}</span>
                </div>
                <div className="flex justify-between text-gray-500 uppercase tracking-widest text-xs">
                  <span>Tax (10%)</span>
                  <span>{formatCurrency(invoice.tax, settings?.currency)}</span>
                </div>
                <div className="flex justify-between pt-4 text-3xl font-black text-gray-900 uppercase">
                  <span>Total</span>
                  <span>{formatCurrency(invoice.total, settings?.currency)}</span>
                </div>
              </div>
              
              <div className="p-6 bg-blue-50 rounded-2xl border border-blue-100 flex items-start gap-4 text-left print:hidden">
                <div className="p-2 bg-white rounded-lg text-blue-600 shadow-sm">
                  <Clock className="w-5 h-5" />
                </div>
                <div>
                  <h4 className="font-bold text-blue-900 text-sm">Payment Status</h4>
                  <p className="text-xs text-blue-700 mt-1">This invoice is currently in {invoice.status} status. Please reconcile upon payment.</p>
                </div>
              </div>
            </div>
          </div>
          
          <div className="pt-12 text-center">
            <p className="text-xs text-gray-400 uppercase font-black tracking-[0.3em]">Thank you for your business</p>
          </div>
        </div>
      </div>
    </div>
  );
}
