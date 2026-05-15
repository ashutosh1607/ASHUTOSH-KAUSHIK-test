import { useState, useEffect } from "react";
import { collection, query, orderBy, limit, getDocs, where } from "firebase/firestore";
import { db } from "../lib/firebase";
import { Invoice, Customer } from "../types";
import { formatCurrency } from "../lib/utils";
import { FileText, Users, DollarSign, TrendingUp, ArrowRight, Loader2 } from "lucide-react";
import { Link } from "react-router-dom";
import { format } from "date-fns";

export function Dashboard() {
  const [stats, setStats] = useState({ totalRevenue: 0, invoiceCount: 0, customerCount: 0, pendingAmount: 0 });
  const [recentInvoices, setRecentInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchDashboardData() {
      try {
        const invoicesSnap = await getDocs(collection(db, "invoices"));
        const customersSnap = await getDocs(collection(db, "customers"));
        
        let revenue = 0;
        let pending = 0;
        const invoices: Invoice[] = [];
        
        invoicesSnap.forEach((doc) => {
          const data = doc.data() as Invoice;
          invoices.push({ ...data, id: doc.id });
          revenue += data.total;
          if (data.status === 'pending') {
            pending += data.total;
          }
        });

        // Sort and limit recent invoices
        const sortedInvoices = invoices.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
        setRecentInvoices(sortedInvoices.slice(0, 5));
        
        setStats({
          totalRevenue: revenue,
          invoiceCount: invoicesSnap.size,
          customerCount: customersSnap.size,
          pendingAmount: pending
        });
      } catch (error) {
        console.error("Error fetching dashboard data:", error);
      } finally {
        setLoading(false);
      }
    }
    fetchDashboardData();
  }, []);

  const statCards = [
    { name: 'Total Revenue', value: formatCurrency(stats.totalRevenue), icon: DollarSign, color: 'bg-green-100 text-green-600' },
    { name: 'Invoices Issued', value: stats.invoiceCount, icon: FileText, color: 'bg-blue-100 text-blue-600' },
    { name: 'Total Customers', value: stats.customerCount, icon: Users, color: 'bg-purple-100 text-purple-600' },
    { name: 'Pending Payments', value: formatCurrency(stats.pendingAmount), icon: TrendingUp, color: 'bg-amber-100 text-amber-600' },
  ];

  if (loading) {
    return <div className="flex justify-center py-20"><Loader2 className="w-8 h-8 text-blue-500 animate-spin" /></div>;
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
        <p className="text-gray-500 mt-1">Here's what's happening with your ecommerce invoices.</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {statCards.map((stat) => (
          <div key={stat.name} className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div className={`p-3 rounded-xl ${stat.color}`}>
              <stat.icon className="w-6 h-6" />
            </div>
            <div>
              <p className="text-sm text-gray-500 font-medium">{stat.name}</p>
              <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Recent Invoices */}
        <div className="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div className="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-gray-900">Recent Invoices</h2>
            <Link to="/invoices" className="text-blue-600 text-sm font-medium hover:underline flex items-center gap-1">
              View all <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead className="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                  <th className="px-6 py-4">Invoice</th>
                  <th className="px-6 py-4">Customer</th>
                  <th className="px-6 py-4">Date</th>
                  <th className="px-6 py-4 text-right">Amount</th>
                  <th className="px-6 py-4">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {recentInvoices.map((invoice) => (
                  <tr key={invoice.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4 font-medium text-blue-600">
                      <Link to={`/invoices/${invoice.id}`}>#{invoice.invoiceNumber}</Link>
                    </td>
                    <td className="px-6 py-4 text-gray-600">{invoice.customerName}</td>
                    <td className="px-6 py-4 text-gray-500">{format(new Date(invoice.date), "MMM d, yyyy")}</td>
                    <td className="px-6 py-4 text-right font-semibold text-gray-900">{formatCurrency(invoice.total)}</td>
                    <td className="px-6 py-4">
                      <span className={`px-2.5 py-1 rounded-full text-xs font-medium ${
                        invoice.status === 'paid' ? 'bg-green-100 text-green-700' : 
                        invoice.status === 'pending' ? 'bg-amber-100 text-amber-700' : 
                        'bg-red-100 text-red-700'
                      }`}>
                        {invoice.status}
                      </span>
                    </td>
                  </tr>
                ))}
                {recentInvoices.length === 0 && (
                  <tr>
                    <td colSpan={5} className="px-6 py-10 text-center text-gray-500">No invoices generated yet.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="space-y-6">
          <div className="bg-gradient-to-br from-blue-600 to-blue-700 p-8 rounded-2xl shadow-xl text-white">
            <h3 className="text-xl font-bold mb-2">Generate Invoice</h3>
            <p className="text-blue-100 text-sm mb-6">Create a professional invoice for your customers in seconds.</p>
            <Link to="/invoices/new" className="block w-full text-center py-3 bg-white text-blue-600 rounded-xl font-bold hover:bg-blue-50 transition-colors shadow-lg">
              New Invoice
            </Link>
          </div>
          
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 className="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
            <div className="space-y-3">
              <Link to="/customers" className="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-50 group">
                <span className="text-gray-600 font-medium">Manage Customers</span>
                <ChevronRight className="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" />
              </Link>
              <Link to="/settings" className="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors border border-gray-50 group">
                <span className="text-gray-600 font-medium">System Settings</span>
                <ChevronRight className="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function ChevronRight({ className }: { className?: string }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
      <path d="m9 18 6-6-6-6"/>
    </svg>
  );
}
