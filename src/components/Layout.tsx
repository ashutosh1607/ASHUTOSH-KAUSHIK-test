import { Link, Outlet, useLocation } from "react-router-dom";
import { User } from "firebase/auth";
import { 
  LayoutDashboard, 
  Users, 
  FileText, 
  Settings as SettingsIcon, 
  PlusCircle, 
  LogOut,
  ChevronRight
} from "lucide-react";
import { logout } from "../lib/firebase";
import { cn } from "../lib/utils";

interface LayoutProps {
  user: User;
}

export function Layout({ user }: LayoutProps) {
  const location = useLocation();

  const navigation = [
    { name: 'Dashboard', href: '/', icon: LayoutDashboard },
    { name: 'Invoices', href: '/invoices', icon: FileText },
    { name: 'Customers', href: '/customers', icon: Users },
    { name: 'Settings', href: '/settings', icon: SettingsIcon },
  ];

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Sidebar */}
      <aside className="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col fixed inset-y-0">
        <div className="p-6">
          <div className="flex items-center gap-3 mb-8">
            <div className="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">
              I
            </div>
            <span className="font-bold text-xl text-gray-900">InvoiceCRM</span>
          </div>

          <nav className="space-y-1">
            {navigation.map((item) => {
              const isActive = location.pathname === item.href || (item.href !== '/' && location.pathname.startsWith(item.href));
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={cn(
                    "flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group",
                    isActive 
                      ? "bg-blue-50 text-blue-600 font-medium shadow-sm shadow-blue-100" 
                      : "text-gray-500 hover:bg-gray-50 hover:text-gray-900"
                  )}
                >
                  <item.icon className={cn("w-5 h-5", isActive ? "text-blue-600" : "text-gray-400 group-hover:text-gray-600")} />
                  {item.name}
                </Link>
              );
            })}
          </nav>
        </div>

        <div className="mt-auto p-6 space-y-4">
          <Link 
            to="/invoices/new"
            className="flex items-center justify-center gap-2 w-full py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200"
          >
            <PlusCircle className="w-5 h-5" />
            New Invoice
          </Link>

          <div className="pt-6 border-t border-gray-100">
            <div className="flex items-center gap-3 mb-4 px-2">
              <img src={user.photoURL || `https://ui-avatars.com/api/?name=${user.displayName}`} alt={user.displayName || 'User'} className="w-10 h-10 rounded-full" />
              <div className="flex-1 overflow-hidden">
                <p className="text-sm font-medium text-gray-900 truncate">{user.displayName}</p>
                <p className="text-xs text-gray-500 truncate">{user.email}</p>
              </div>
            </div>
            <button 
              onClick={logout}
              className="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
            >
              <LogOut className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              Sign Out
            </button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 md:pl-64">
        <div className="max-w-6xl mx-auto p-4 md:p-8">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
