export const LEAD_STATUSES = [
  { value: 'new', label: 'New', color: 'bg-blue-100 text-blue-800' },
  { value: 'contacted', label: 'Contacted', color: 'bg-yellow-100 text-yellow-800' },
  { value: 'qualified', label: 'Qualified', color: 'bg-green-100 text-green-800' },
  { value: 'converted', label: 'Converted', color: 'bg-purple-100 text-purple-800' },
  { value: 'recycled', label: 'Recycled', color: 'bg-gray-100 text-gray-800' },
] as const;

export const LEAD_SCORES = [
  { value: 'hot', label: 'Hot', color: 'bg-red-100 text-red-800' },
  { value: 'warm', label: 'Warm', color: 'bg-orange-100 text-orange-800' },
  { value: 'cold', label: 'Cold', color: 'bg-blue-100 text-blue-800' },
] as const;

export const ACTIVITY_TYPES = [
  { value: 'call', label: 'Call' },
  { value: 'email', label: 'Email' },
  { value: 'meeting', label: 'Meeting' },
  { value: 'task', label: 'Task' },
  { value: 'note', label: 'Note' },
  { value: 'status_change', label: 'Status Change' },
] as const;

export const ADDRESS_TYPES = [
  { value: 'billing', label: 'Billing' },
  { value: 'shipping', label: 'Shipping' },
  { value: 'other', label: 'Other' },
] as const;
