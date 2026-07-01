// Single source of truth for ChannaCar's real-world business identity.
// Used by the Schema.org / JSON-LD structured data (see layouts/default.vue).
// Keep this in sync with the contact details shown in marketing-content.ts.
//
// TODO(business): fill in the fields marked PLACEHOLDER below with the real
// values, then remove this note. Search engines and AI answer engines (GEO)
// use these for the knowledge graph, local pack, and citations — accuracy and
// cross-channel consistency (NAP) matter.

export const businessInfo = {
  // Canonical legal/brand name. The site copy also uses the spelling "ChanaaCar";
  // alternateName covers both so search engines resolve them to one entity.
  name: 'ChannaCar',
  alternateName: 'ChanaaCar',
  legalName: 'ChannaCar',
  description:
    'Agence de location de voiture à Marrakech : prise en charge à l’aéroport Ménara, location longue durée et large flotte de véhicules. Réservation simple et tarifs transparents.',

  email: 'contact@chanaacar.com',
  // E.164 format for the primary line; the second line is a backup.
  telephone: '+212666623645',
  secondaryTelephone: '+212661232902',
  // WhatsApp line (E.164). Used by the floating WhatsApp button and contact info.
  whatsapp: '+212615933936',

  logo: '/images/chanaa-car-logo.png',
  image: '/images/hero-banner.jpg',

  address: {
    streetAddress: 'PLACEHOLDER — rue et numéro', // TODO(business)
    addressLocality: 'Marrakech',
    addressRegion: 'Marrakech-Safi',
    postalCode: 'PLACEHOLDER', // TODO(business) e.g. 40000
    addressCountry: 'MA',
  },

  // Approx. geo for Marrakech; replace with the agency's exact coordinates.
  geo: {
    latitude: 31.6295, // TODO(business) exact lat
    longitude: -7.9811, // TODO(business) exact lng
  },

  currenciesAccepted: 'MAD',
  priceRange: 'MAD',
  // 7 days a week, 24h assistance (per contactContent in marketing-content.ts).
  opens: '00:00',
  closes: '23:59',

  areaServed: [
    'Marrakech',
    'Aéroport Marrakech Ménara',
    'Guéliz',
    'Médina',
    'Palmeraie',
    'Hivernage',
    'Casablanca',
    'Agadir',
    'Essaouira',
  ],

  // Public profiles — strengthens entity disambiguation for GEO/knowledge graph.
  // TODO(business): a Trustpilot domain verification already exists in DNS —
  // add the public Trustpilot profile URL here too once available.
  sameAs: [
    'https://www.facebook.com/profile.php?id=61578329224592',
    'https://www.instagram.com/chanaa_car/',
    'https://www.snapchat.com/add/abde_999',
    'https://www.linkedin.com/company/chan%C3%A2a-car/',
  ],
} as const
