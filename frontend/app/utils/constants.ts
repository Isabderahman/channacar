export const CATEGORY_OPTIONS = [
  'Citadines',
  'Économique',
  'Familiale',
  'Mini urbaine',
  'SUV',
  'Utilitaire',
] as const

export const TRANSMISSION_OPTIONS = [
  { label: 'Manual', value: 'manual' },
  { label: 'Automatic', value: 'auto' },
] as const

export const FUEL_OPTIONS = [
  { label: 'Diesel', value: 'diesel' },
  { label: 'Petrol', value: 'petrol' },
  { label: 'Hybrid', value: 'hybrid' },
  { label: 'Electric', value: 'electric' },
] as const

export const CAR_STATUS_OPTIONS = [
  { label: 'Available', value: 'available' },
  { label: 'Rented', value: 'rented' },
  { label: 'Maintenance', value: 'maintenance' },
] as const

export const RESERVATION_STATUS_OPTIONS = [
  { label: 'Pending', value: 'pending' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Ongoing', value: 'ongoing' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
] as const

export const PAYMENT_STATUS_OPTIONS = [
  { label: 'Unpaid', value: 'unpaid' },
  { label: 'Deposit', value: 'deposit' },
  { label: 'Paid', value: 'paid' },
] as const

export const RESERVATION_SOURCE_OPTIONS = [
  { label: 'Website', value: 'web' },
  { label: 'Phone', value: 'phone' },
  { label: 'Walk-in', value: 'walkin' },
] as const

export const SEASON_PRICE_TYPE_OPTIONS = [
  { label: 'Multiplier', value: 'multiplier' },
  { label: 'Fixed price', value: 'fixed' },
] as const

export const ADMIN_NAV_ITEMS = [
  { label: 'Dashboard', to: '/admin/dashboard', icon: 'dashboard' },
  { label: 'Cars', to: '/admin/cars', icon: 'car' },
  { label: 'Reservations', to: '/admin/reservations', icon: 'calendar' },
  { label: 'Clients', to: '/admin/clients', icon: 'people' },
  { label: 'Seasons', to: '/admin/seasons', icon: 'clock' },
  { label: 'Locations', to: '/admin/locations', icon: 'map-pin' },
  { label: 'Extras', to: '/admin/extras', icon: 'plus' },
  { label: 'Testimonials', to: '/admin/testimonials', icon: 'star' },
  { label: 'Settings', to: '/admin/settings', icon: 'settings' },
] as const

export const HOME_FEATURES = [
  {
    title: 'Fast approvals',
    description: 'Shorten the path from inquiry to confirmed reservation with a cleaner booking flow.',
    icon: 'check-circle',
  },
  {
    title: 'Marrakech-ready fleet',
    description: 'Highlight climate control, luggage space, and airport pickup across the best-fit cars.',
    icon: 'map-pin',
  },
  {
    title: 'Admin control',
    description: 'Switch between reservations, fleet changes, client records, and moderation from one shell.',
    icon: 'dashboard',
  },
] as const

export const PUBLIC_TESTIMONIAL_FALLBACK = [
  {
    id: 1,
    full_name: 'Leila B.',
    content:
      'Pickup was smooth, the SUV was exactly as described, and the reservation updates felt clear the whole way through.',
    stars: 5,
  },
  {
    id: 2,
    full_name: 'Youssef A.',
    content:
      'The team handled a late timing change without friction. The booking flow made it easy to trust the details.',
    stars: 5,
  },
  {
    id: 3,
    full_name: 'Sofia R.',
    content:
      'Good pricing, clean car, and a much more polished experience than the usual rental WhatsApp back-and-forth.',
    stars: 4,
  },
] as const
