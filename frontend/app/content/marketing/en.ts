import {
  contactEmails,
  contactPhones,
  contactWhatsapp,
  type MarketingContent,
} from './shared'

const en: MarketingContent = {
  dateLocale: 'en-GB',

  heroFeatures: [
    {
      title: 'Transparent pricing',
      description: 'A clear offer, visible terms and a more direct booking.',
    },
    {
      title: 'Marrakech and the airport',
      description: 'A service built for customers arriving in, leaving from or travelling around Morocco.',
    },
  ],

  marrakech: {
    seo: {
      title: 'Car rental in Marrakech | ChanaaCar',
      description:
        'Why rent a car in Marrakech, and how ChanaaCar makes getting around Morocco easier.',
    },
    title: 'Car rental in Marrakech at the best price',
    paragraphs: [
      "Marrakech is one of Morocco's most visited destinations. Renting a car lets you discover the city and its surroundings at your own pace.",
      'Whether you want to visit the Medina, Gueliz, the Palmeraie, the Majorelle Garden or the Atlas Mountains, or head off to Essaouira, Agadir or Ouarzazate, ChanaaCar has vehicles to suit every budget.',
    ],
    hero: {
      eyebrow: 'Marrakech rental',
      primaryLabel: 'See the cars',
      secondaryLabel: 'Contact us',
    },
    narrative: {
      eyebrow: 'Getting around Marrakech',
      title: 'Move freely around the city and beyond.',
      intro:
        'A car lets you fit in your visits, meetings and day trips with far more flexibility.',
    },
    reasons: {
      title: 'Why rent a car in Marrakech?',
      items: [
        'Freedom to go where you want',
        'Time saved',
        'Comfort for the whole family',
        'Great-value rates',
        'Easy access to the main tourist regions',
      ],
    },
  },

  airport: {
    seo: {
      title: 'Car rental at Marrakech Menara Airport | ChanaaCar',
      description:
        'Car rental with fast vehicle handover at Marrakech Menara Airport.',
    },
    title: 'Car rental at Marrakech Menara Airport',
    paragraphs: [
      'Save time from the moment you land in Marrakech. Our team meets you at Marrakech Menara Airport and hands over your vehicle quickly.',
      'We keep track of your flight time so your car is handed over in the best possible conditions.',
    ],
    hero: {
      eyebrow: 'Marrakech Menara Airport',
      primaryLabel: 'Book a vehicle',
      secondaryLabel: 'Call now',
    },
    narrative: {
      eyebrow: 'Arriving in Marrakech',
      title: 'An easier pick-up right outside the airport.',
      intro:
        'As soon as you land, you skip the wait and start your stay with a car that is ready to go.',
    },
    advantages: {
      title: 'Benefits',
      items: [
        'Meet & greet at the airport',
        'Fast vehicle handover',
        'Help if your flight is delayed',
        'Easy return before you fly home',
      ],
    },
  },

  longTerm: {
    seo: {
      title: 'Long-term car rental in Morocco | ChanaaCar',
      description:
        'Long-term rental solutions for individuals, professionals, expats and companies.',
    },
    title: 'Long-term car rental in Morocco',
    paragraphs: [
      'Need a vehicle for several weeks or several months?',
      'ChanaaCar offers long-term rental solutions tailored to individuals, professionals, expats and companies.',
    ],
    hero: {
      eyebrow: 'Long term',
      primaryLabel: 'Request a quote',
      secondaryLabel: 'Discover the fleet',
    },
    narrative: {
      eyebrow: 'Long-term offers',
      title: 'A steadier solution for long stays and business needs.',
      intro:
        'Over several weeks or months, the aim is a reliable solution with clear, simple terms.',
    },
    advantages: {
      title: 'Benefits',
      items: [
        'Preferential rates',
        'Recent vehicles',
        'Maintenance included, depending on the contract',
        'Assistance available',
        'Hassle-free management',
      ],
    },
  },

  about: {
    seo: {
      title: 'About ChanaaCar',
      description:
        'Discover the mission, experience and values of the ChanaaCar agency.',
    },
    title: 'About ChanaaCar',
    paragraphs: [
      'ChanaaCar is an agency specialising in car rental in Marrakech and in several other cities across Morocco.',
      'Our mission is to give our customers quality service, reliable vehicles and a simple, transparent rental experience.',
      'Since we started, we have been looking after travellers from all over the world, as well as professionals looking for mobility solutions that fit their needs.',
    ],
    hero: {
      eyebrow: 'About',
      primaryLabel: 'See the fleet',
      secondaryLabel: 'Talk to the team',
    },
    narrative: {
      eyebrow: 'The agency',
      title: 'A local team that puts clarity and availability first.',
      intro:
        'We look after travellers, families and professionals with a simple, fast and easy-to-follow service.',
    },
    values: {
      title: 'Our values',
      cards: [
        {
          title: 'Transparency',
          description: 'Clear prices with no unpleasant surprises.',
        },
        {
          title: 'Quality',
          description: 'Recent, carefully maintained vehicles.',
        },
        {
          title: 'Customer satisfaction',
          description: 'Personal support before, during and after your rental.',
        },
        {
          title: 'Responsiveness',
          description: 'A team on hand to answer all your requests quickly.',
        },
      ],
    },
  },

  faq: {
    seo: {
      title: 'FAQ | ChanaaCar',
      description:
        'Frequently asked questions about documents, the deposit, insurance, cancellations and collecting your vehicle.',
    },
    title: 'Frequently asked questions',
    subtitle:
      'The essential answers to quickly understand how renting with ChanaaCar works.',
    hero: {
      eyebrow: 'FAQ',
      primaryLabel: 'Contact ChanaaCar',
      secondaryLabel: 'See the cars',
    },
    items: [
      {
        question: 'How much does it cost to rent a car?',
        answer:
          'Our rates start at 300 MAD/day for the economy category and vary with the vehicle, the length of the rental and the season. Long-term rentals get decreasing rates. No hidden fees.',
      },
      {
        question: 'What is the minimum age, and how long must I have held my licence?',
        answer:
          'The driver must be at least 21 years old and have held a valid driving licence for at least 2 years.',
      },
      {
        question: 'Which documents do I need to rent a car?',
        answer:
          'A valid ID (national ID card or passport), a driving licence and a means of payment.',
      },
      {
        question: 'Is mileage limited?',
        answer:
          'Most of our rentals include mileage suited to your itinerary. Unlimited mileage is available on request, depending on the category and the length of the rental.',
      },
      {
        question: 'Can I pick the car up at the airport?',
        answer:
          'Yes. We deliver and hand over vehicles at Marrakech Menara Airport.',
      },
      {
        question: 'Is insurance included?',
        answer:
          'Yes, our vehicles are covered under the terms of the rental agreement.',
      },
      {
        question: 'Is there a deposit?',
        answer: 'Yes, the amount depends on the category of the vehicle you rent.',
      },
      {
        question: 'Can I add an extra driver?',
        answer: 'Yes, as long as the required documents are provided.',
      },
      {
        question: 'What should I do if I break down or have an accident?',
        answer:
          'Contact our assistance team straight away — they are available 24/7.',
      },
      {
        question: 'Can I cancel my booking?',
        answer:
          'Yes, under the cancellation terms given when you book.',
      },
    ],
  },

  contact: {
    seo: {
      title: 'Contact | ChanaaCar',
      description:
        'Contact details, availability and an enquiry form to reach the ChanaaCar team.',
    },
    title: 'Contact ChanaaCar',
    subtitle:
      'Our team is here to answer all your questions and help you with your booking.',
    hero: {
      eyebrow: 'Contact',
      primaryLabel: 'Book now',
      secondaryLabel: 'FAQ',
    },
    groups: [
      { kind: 'phone', label: 'Phone', values: contactPhones },
      { kind: 'whatsapp', label: 'WhatsApp', values: contactWhatsapp },
      { kind: 'email', label: 'Email', values: contactEmails },
      { kind: 'address', label: 'Address', values: ['Marrakech, Morocco'] },
      {
        kind: 'hours',
        label: 'Hours',
        values: ['Open 7 days a week', '24/7 assistance'],
      },
    ],
  },

  pageCta: {
    title: 'Need a quick, clear quote?',
    description:
      'Browse our fleet, get in touch with the team, or start a booking in just a few steps.',
    primaryLabel: 'Discover the fleet',
    secondaryLabel: 'Contact ChanaaCar',
  },

  contactForm: {
    title: 'Send my request',
    subtitle:
      'Tell us your dates and what you need: your request opens in WhatsApp and our team replies quickly, by phone or on WhatsApp.',
    labels: {
      nom_complet: 'Full name',
      telephone: 'Phone',
      email: 'Email',
      date_depart: 'Pick-up date',
      date_retour: 'Return date',
      lieu_prise_en_charge: 'Pick-up location',
      lieu_retour: 'Return location',
      message: 'Message',
    },
    labelSeparator: ': ',
    submit: 'Send my request',
    statusTitle: 'Request ready in WhatsApp',
    statusText:
      'Your request is ready in WhatsApp — send it there and we’ll reply quickly.',
    statusLink: 'Open WhatsApp',
    whatsappIntro: 'Hello ChanaaCar, here is my rental request:',
    vehiclePrefill:
      'Hello ChanaaCar, I’d like some information about the {vehicle}. Is it available for my dates?',
  },
}

export default en
