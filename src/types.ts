export interface BusinessSettings {
  adminName: string;
  logoUrl?: string;
  businessAddress?: string;
  email?: string;
  phone?: string;
  currency: string;
}

export interface Customer {
  id: string;
  name: string;
  email?: string;
  phone?: string;
  address?: string;
  createdAt: string;
}

export interface InvoiceItem {
  description: string;
  quantity: number;
  price: number;
}

export interface Invoice {
  id: string;
  invoiceNumber: string;
  customerId: string;
  customerName: string;
  items: InvoiceItem[];
  subtotal: number;
  tax: number;
  total: number;
  date: string;
  status: 'pending' | 'paid' | 'cancelled';
}
