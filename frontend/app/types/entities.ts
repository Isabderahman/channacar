export type NumericValue = number | string

export type CarStatus = 'available' | 'rented' | 'maintenance'
export type FuelType = 'diesel' | 'petrol' | 'hybrid' | 'electric'
export type PaymentStatus = 'unpaid' | 'deposit' | 'paid'
export type ReservationSource = 'web' | 'phone' | 'walkin'
export type ReservationStatus = 'pending' | 'confirmed' | 'ongoing' | 'completed' | 'cancelled'
export type SeasonPriceType = 'multiplier' | 'fixed'
export type TransmissionType = 'manual' | 'auto'

export interface Category {
  id: number
  name: string
}

export interface CarImage {
  id: number
  car_id: number
  path: string
  is_thumbnail: boolean
  sort_order: number
}

export interface Car {
  id: number
  slug?: string
  name: string
  brand: string
  year: number
  category_id: number
  category?: Category | null
  doors: number
  seats: number
  luggage: number
  transmission: TransmissionType
  fuel: FuelType
  climatisation: boolean
  gps: boolean
  base_price_per_day: NumericValue
  insurance_price_per_day?: NumericValue | null
  status: CarStatus
  registration_number: string
  is_active: boolean
  images?: CarImage[]
  created_at?: string
  updated_at?: string
}

export interface Client {
  id: number
  full_name: string
  birth_date?: string | null
  birth_place?: string | null
  address?: string | null
  phone: string
  whatsapp?: string | null
  email?: string | null
  driver_license: string
  license_issued_at?: string | null
  license_issued_place?: string | null
  passport_number?: string | null
  cin_number?: string | null
  reservations?: Reservation[]
  created_at?: string
  updated_at?: string
}

export interface ContractDetails {
  prolongation_date?: string | null
  prolongation_time?: string | null
  prolongation_location?: string | null
  km_depart?: string | null
  km_arrivee?: string | null
  fuel_depart?: string | null
  fuel_retour?: string | null
  condition_depart?: string | null
  condition_retour?: string | null
  personnes_transportees?: string | null
  suppression_franchise?: boolean | null
  divers_note?: string | null
}

export interface SecondDriver {
  full_name?: string | null
  birth_date?: string | null
  birth_place?: string | null
  address?: string | null
  phone?: string | null
  driver_license?: string | null
  license_issued_at?: string | null
  license_issued_place?: string | null
  passport_number?: string | null
  cin_number?: string | null
}

export interface PickupLocation {
  id: number
  name: string
  address: string
  delivery_fee?: NumericValue
  is_active: boolean
}

export interface Extra {
  id: number
  name: string
  icon?: string | null
  price_per_day: NumericValue
  is_active: boolean
}

export interface Season {
  id: number
  name: string
  date_from: string
  date_to: string
  price_type: SeasonPriceType
  price_value: NumericValue
  created_at?: string
  updated_at?: string
}

export interface Testimonial {
  id: number
  full_name: string
  content: string
  stars: number
  is_visible: boolean
  created_at?: string
  updated_at?: string
}

export interface Reservation {
  id: number
  reservation_number: string
  client_id: number
  car_id: number
  pickup_location_id: number
  dropoff_location_id: number
  pickup_date: string
  pickup_time: string
  dropoff_date: string
  dropoff_time: string
  status: ReservationStatus
  payment_status: PaymentStatus
  deposit_amount: NumericValue
  total_price: NumericValue
  insurance_total?: NumericValue
  delivery_total?: NumericValue
  payment_method?: 'cash' | 'cheque' | null
  contract_number?: string | null
  contract_generated_at?: string | null
  second_driver?: SecondDriver | null
  contract_details?: ContractDetails | null
  driver_license_path?: string | null
  driver_license_verso_path?: string | null
  identity_path?: string | null
  identity_verso_path?: string | null
  source: ReservationSource
  notes?: string | null
  client?: Client | null
  car?: Car | null
  pickup_location?: PickupLocation | null
  pickupLocation?: PickupLocation | null
  dropoff_location?: PickupLocation | null
  dropoffLocation?: PickupLocation | null
  extras?: Extra[]
  created_at?: string
  updated_at?: string
}

export interface AdminUser {
  id: number
  name: string
  email: string
  role?: string
  created_at?: string
  updated_at?: string
}

export interface LoginResponse {
  token: string
  user: AdminUser
}

export interface ResourceResponse<T> {
  data: T
}

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string | null
  from: number | null
  last_page: number
  last_page_url: string | null
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}

export interface DashboardStats {
  cars: {
    total: number
    active: number
    available: number
    rented: number
    maintenance: number
  }
  reservations: {
    total: number
    pending: number
    confirmed: number
    ongoing: number
    completed: number
    cancelled: number
  }
  clients: number
  locations: number
  extras: number
  testimonials: {
    total: number
    visible: number
  }
  revenue: {
    paid_total: NumericValue
    deposits_collected: NumericValue
  }
  today: {
    pickups: number
    dropoffs: number
  }
}
