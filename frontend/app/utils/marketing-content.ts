export interface MarketingCard {
  description: string
  title: string
}

export interface MarketingFaqItem {
  answer: string
  question: string
}

export interface MarketingLink {
  label: string
  to: string
}

export interface MarketingContactGroup {
  label: string
  values: string[]
}

export const marketingNavLinks: MarketingLink[] = [
  { label: 'Accueil', to: '/' },
  { label: 'Nos Véhicules', to: '/cars' },
  { label: 'Location Marrakech', to: '/location-marrakech' },
  { label: 'Location Aéroport', to: '/location-aeroport-marrakech-menara' },
  { label: 'Longue Durée', to: '/location-longue-duree' },
  { label: 'À Propos', to: '/a-propos' },
  { label: 'FAQ', to: '/faq' },
  { label: 'Contact', to: '/contact' },
]

export const marketingFooterInfo = [
  'Assistance 24h/24',
  'Réservation rapide',
  'Tarifs transparents',
  'Véhicules récents',
]

export const homeStaticContent = {
  title: 'Location de Voiture à Marrakech — Luxe & Confort',
  subtitle:
    "Choisissez l'élégance, roulez en toute liberté. ChanaaCar vous propose des véhicules récents et entretenus, de l'économique au premium, livrés à l'aéroport Marrakech Menara, à votre hôtel ou en ville. Tarifs transparents à partir de 300 DH/jour, assistance 24h/24 et réservation en quelques minutes.",
  primaryAction: { label: 'Réserver maintenant', to: '/cars' },
  secondaryAction: { label: 'Découvrir notre flotte', to: '/cars' },
  whyChooseTitle: 'Pourquoi choisir ChanaaCar ?',
  whyChooseText:
    "Chez ChanaaCar, votre confort et votre tranquillité passent avant tout. Voyageur en quête de découverte, professionnel en déplacement ou famille en vacances : nous mettons à votre disposition une flotte récente, des prix clairs et un accompagnement personnalisé avant, pendant et après votre location. Une expérience de location simple, fiable et sans mauvaises surprises.",
  advantages: [
    'Véhicules récents, propres et rigoureusement entretenus',
    "Livraison gratuite à l'aéroport Marrakech Menara selon conditions",
    'Assistance routière 24h/24 et 7j/7',
    'Kilométrage flexible adapté à votre itinéraire',
    'Réservation rapide en ligne ou via WhatsApp',
    'Tarifs compétitifs et transparents, sans frais cachés',
    'Options sur mesure : GPS, siège enfant, conducteur supplémentaire',
  ],
  services: [
    {
      title: 'Location courte durée',
      description:
        "Une journée, un week-end ou quelques jours : louez le véhicule idéal à partir de 300 DH/jour, selon vos besoins.",
    },
    {
      title: 'Location longue durée',
      description:
        'Des tarifs dégressifs avantageux pour les locations mensuelles, les expatriés et les professionnels.',
    },
    {
      title: "Livraison à l'aéroport",
      description:
        "Notre équipe vous accueille à l'aéroport Marrakech Menara et vous remet votre véhicule dès votre arrivée.",
    },
    {
      title: 'Livraison à votre hôtel',
      description: 'Nous déposons votre voiture directement à votre hébergement, où que vous séjourniez.',
    },
    {
      title: 'Véhicules premium & VIP',
      description:
        'Mercedes, Audi, Range Rover, Porsche : choisissez le prestige pour vos occasions spéciales et déplacements business.',
    },
    {
      title: 'Assistance 24/7',
      description:
        'Une équipe réactive disponible à tout moment pour vous accompagner tout au long de votre location.',
    },
  ] satisfies MarketingCard[],
  travelTitle: 'Explorez le Maroc en toute liberté',
  travelText:
    "De la Médina aux Jardins Majorelle, de l'Atlas jusqu'à Essaouira, Agadir, Casablanca, Rabat ou Ouarzazate : prenez la route à votre rythme avec un véhicule confortable, fiable et adapté à chaque étape de votre voyage.",
}

export const fleetContent = {
  title: 'Notre flotte de véhicules',
  subtitle:
    "Choisissez le véhicule qui correspond à vos besoins et profitez d'une expérience de conduite confortable partout au Maroc.",
  categories: [
    {
      title: 'Catégorie Économique — à partir de 300 DH/jour',
      description:
        'Idéale pour la ville et les petits budgets : compacte, économe en carburant et facile à conduire.',
      image: '/images/car-2.jpg',
      vehicles: [
        'Dacia Logan',
        'Dacia Sandero',
        'Renault Clio 5',
        'Peugeot 208',
        'Hyundai Accent',
        'Opel Corsa',
      ],
    },
    {
      title: 'Catégorie Familiale — à partir de 450 DH/jour',
      description:
        'Espace, confort et coffre généreux pour les familles et les longs trajets à travers le Maroc.',
      image: '/images/car-4.jpg',
      vehicles: ['Dacia Jogger 7 Places', 'Volkswagen T-Roc', 'Hyundai Tucson'],
    },
    {
      title: 'Catégorie SUV & Premium — à partir de 700 DH/jour',
      description:
        'Puissance, prestige et élégance pour vos occasions spéciales et déplacements professionnels.',
      image: '/images/car-6.jpg',
      vehicles: [
        'Volkswagen Golf 8',
        'Audi Q3',
        'Mercedes Classe A',
        'Volkswagen Touareg',
        'Range Rover Evoque',
        'Porsche Macan',
      ],
    },
  ],
  includedTitle: 'Inclus avec chaque location',
  included: [
    'Véhicule propre et contrôlé',
    'Assistance routière',
    'Contrat transparent',
    'Service client disponible',
  ],
}

export const marrakechContent = {
  title: 'Location de voiture à Marrakech au meilleur prix',
  paragraphs: [
    "Marrakech est l'une des destinations les plus visitées du Maroc. Louer une voiture vous permet de découvrir la ville et ses environs à votre rythme.",
    "Que vous souhaitiez visiter la Médina, Guéliz, la Palmeraie, les Jardins Majorelle, l'Atlas ou partir vers Essaouira, Agadir ou Ouarzazate, ChanaaCar met à votre disposition des véhicules adaptés à tous les budgets.",
  ],
  reasons: [
    'Liberté de déplacement',
    'Gain de temps',
    'Confort pour toute la famille',
    'Tarifs avantageux',
    'Accès facile aux régions touristiques',
  ],
}

export const airportContent = {
  title: "Location de voiture à l'aéroport Marrakech Menara",
  paragraphs: [
    "Gagnez du temps dès votre arrivée à Marrakech. Notre équipe vous accueille à l'aéroport Marrakech Menara et vous remet votre véhicule rapidement.",
    "Nous suivons l'heure de votre vol afin d'assurer une remise du véhicule dans les meilleures conditions.",
  ],
  advantages: [
    "Accueil à l'aéroport",
    'Remise rapide du véhicule',
    'Assistance en cas de retard du vol',
    'Retour simplifié avant votre départ',
  ],
}

export const longTermContent = {
  title: 'Location longue durée au Maroc',
  paragraphs: [
    'Vous avez besoin d’un véhicule pour plusieurs semaines ou plusieurs mois ?',
    'ChanaaCar propose des solutions de location longue durée adaptées aux particuliers, professionnels, expatriés et entreprises.',
  ],
  advantages: [
    'Tarifs préférentiels',
    'Véhicules récents',
    'Entretien inclus selon contrat',
    'Assistance disponible',
    'Gestion simplifiée',
  ],
}

export const aboutContent = {
  title: 'À propos de ChanaaCar',
  paragraphs: [
    'ChanaaCar est une agence spécialisée dans la location de voitures à Marrakech et dans plusieurs villes du Maroc.',
    "Notre mission est d'offrir à nos clients un service de qualité, des véhicules fiables et une expérience de location simple et transparente.",
    'Depuis notre création, nous accompagnons des voyageurs du monde entier ainsi que des professionnels à la recherche de solutions de mobilité adaptées à leurs besoins.',
  ],
  values: [
    {
      title: 'Transparence',
      description: 'Des prix clairs et sans mauvaises surprises.',
    },
    {
      title: 'Qualité',
      description: 'Des véhicules récents et soigneusement entretenus.',
    },
    {
      title: 'Satisfaction Client',
      description:
        'Un accompagnement personnalisé avant, pendant et après votre location.',
    },
    {
      title: 'Réactivité',
      description:
        'Une équipe disponible pour répondre rapidement à toutes vos demandes.',
    },
  ] satisfies MarketingCard[],
}

export const faqContent = {
  title: 'Questions fréquentes',
  items: [
    {
      question: 'Quels sont vos tarifs de location ?',
      answer:
        "Nos tarifs débutent à 300 DH/jour pour la catégorie économique et varient selon le véhicule, la durée et la saison. Les locations longue durée bénéficient de tarifs dégressifs. Aucun frais caché.",
    },
    {
      question: 'Quel âge minimum et quelle ancienneté de permis faut-il ?',
      answer:
        "Le conducteur doit être âgé d'au moins 21 ans et titulaire d'un permis de conduire valide depuis au moins 1 an.",
    },
    {
      question: 'Quels documents sont nécessaires pour louer une voiture ?',
      answer:
        "Une pièce d'identité valide (CIN ou passeport), un permis de conduire et un moyen de paiement.",
    },
    {
      question: 'Le kilométrage est-il limité ?',
      answer:
        "La plupart de nos locations incluent un kilométrage adapté à votre itinéraire. Le kilométrage illimité est disponible sur demande selon la catégorie et la durée.",
    },
    {
      question: "Puis-je récupérer la voiture à l'aéroport ?",
      answer:
        "Oui. Nous proposons la livraison et la remise des véhicules à l'aéroport Marrakech Menara.",
    },
    {
      question: "L'assurance est-elle incluse ?",
      answer:
        'Oui, nos véhicules sont couverts selon les conditions du contrat de location.',
    },
    {
      question: 'Existe-t-il une caution ?',
      answer:
        'Oui, le montant dépend de la catégorie du véhicule loué.',
    },
    {
      question: 'Puis-je ajouter un conducteur supplémentaire ?',
      answer:
        'Oui, sous réserve de présentation des documents nécessaires.',
    },
    {
      question: "Que faire en cas de panne ou d'accident ?",
      answer:
        "Contactez immédiatement notre équipe d'assistance disponible 24h/24.",
    },
    {
      question: 'Puis-je annuler ma réservation ?',
      answer:
        "Oui, selon les conditions d'annulation précisées lors de la réservation.",
    },
  ] satisfies MarketingFaqItem[],
}

export const contactContent = {
  title: 'Contactez ChanaaCar',
  subtitle:
    'Notre équipe est à votre disposition pour répondre à toutes vos questions et vous accompagner dans votre réservation.',
  contacts: [
    {
      label: 'Téléphone',
      values: ['+212 6 66 62 36 45', '+212 6 61 23 29 02'],
    },
    {
      label: 'Email',
      values: ['contact@chanaacar.com'],
    },
    {
      label: 'Adresse',
      values: ['Marrakech, Maroc'],
    },
    {
      label: 'Horaires',
      values: ['Disponible 7 jours sur 7', 'Assistance 24h/24'],
    },
  ] satisfies MarketingContactGroup[],
  formButton: 'Envoyer ma demande',
}

export const commonPageCta = {
  title: 'Besoin d’une offre rapide et claire ?',
  description:
    "Consultez notre flotte, contactez l'équipe, ou lancez une réservation avec un parcours plus direct.",
  primaryAction: { label: 'Découvrir la flotte', to: '/cars' },
  secondaryAction: { label: 'Contacter ChanaaCar', to: '/contact' },
}
