import {
  contactEmails,
  contactPhones,
  contactWhatsapp,
  type MarketingContent,
} from './shared'

const fr: MarketingContent = {
  dateLocale: 'fr-FR',

  heroFeatures: [
    {
      title: 'Tarifs transparents',
      description: 'Une offre claire, des conditions visibles, et une réservation plus directe.',
    },
    {
      title: 'Marrakech et aéroport',
      description: 'Des parcours conçus pour les clients qui arrivent, repartent, ou circulent au Maroc.',
    },
  ],

  marrakech: {
    seo: {
      title: 'Location de voiture à Marrakech | ChanaaCar',
      description:
        'Pourquoi louer une voiture à Marrakech et comment ChanaaCar facilite vos déplacements au Maroc.',
    },
    title: 'Location de voiture à Marrakech au meilleur prix',
    paragraphs: [
      "Marrakech est l'une des destinations les plus visitées du Maroc. Louer une voiture vous permet de découvrir la ville et ses environs à votre rythme.",
      "Que vous souhaitiez visiter la Médina, Guéliz, la Palmeraie, les Jardins Majorelle, l'Atlas ou partir vers Essaouira, Agadir ou Ouarzazate, ChanaaCar met à votre disposition des véhicules adaptés à tous les budgets.",
    ],
    hero: {
      eyebrow: 'Location Marrakech',
      primaryLabel: 'Voir les voitures',
      secondaryLabel: 'Nous contacter',
    },
    narrative: {
      eyebrow: 'Déplacements à Marrakech',
      title: 'Circuler librement dans la ville et autour.',
      intro:
        'Une voiture vous permet d’enchaîner vos visites, rendez-vous et excursions avec plus de souplesse.',
    },
    reasons: {
      title: 'Pourquoi louer une voiture à Marrakech ?',
      items: [
        'Liberté de déplacement',
        'Gain de temps',
        'Confort pour toute la famille',
        'Tarifs avantageux',
        'Accès facile aux régions touristiques',
      ],
    },
  },

  airport: {
    seo: {
      title: 'Location voiture aéroport Marrakech Menara | ChanaaCar',
      description:
        "Service de location et de remise rapide de véhicule à l'aéroport Marrakech Menara.",
    },
    title: "Location de voiture à l'aéroport Marrakech Menara",
    paragraphs: [
      "Gagnez du temps dès votre arrivée à Marrakech. Notre équipe vous accueille à l'aéroport Marrakech Menara et vous remet votre véhicule rapidement.",
      "Nous suivons l'heure de votre vol afin d'assurer une remise du véhicule dans les meilleures conditions.",
    ],
    hero: {
      eyebrow: 'Aéroport Marrakech Menara',
      primaryLabel: 'Réserver un véhicule',
      secondaryLabel: 'Appeler maintenant',
    },
    narrative: {
      eyebrow: 'Arrivée à Marrakech',
      title: 'Un retrait plus simple dès la sortie de l’aéroport.',
      intro:
        'Dès l’atterrissage, vous évitez l’attente et démarrez votre séjour avec un véhicule prêt à partir.',
    },
    advantages: {
      title: 'Avantages',
      items: [
        "Accueil à l'aéroport",
        'Remise rapide du véhicule',
        'Assistance en cas de retard du vol',
        'Retour simplifié avant votre départ',
      ],
    },
  },

  longTerm: {
    seo: {
      title: 'Location longue durée au Maroc | ChanaaCar',
      description:
        'Solutions de location longue durée pour particuliers, professionnels, expatriés et entreprises.',
    },
    title: 'Location longue durée au Maroc',
    paragraphs: [
      'Vous avez besoin d’un véhicule pour plusieurs semaines ou plusieurs mois ?',
      'ChanaaCar propose des solutions de location longue durée adaptées aux particuliers, professionnels, expatriés et entreprises.',
    ],
    hero: {
      eyebrow: 'Longue durée',
      primaryLabel: 'Demander une offre',
      secondaryLabel: 'Découvrir la flotte',
    },
    narrative: {
      eyebrow: 'Offres longue durée',
      title: 'Une solution plus stable pour les séjours longs et les besoins pro.',
      intro:
        'Pour plusieurs semaines ou plusieurs mois, l’objectif est de garder une solution fiable avec un cadre clair.',
    },
    advantages: {
      title: 'Avantages',
      items: [
        'Tarifs préférentiels',
        'Véhicules récents',
        'Entretien inclus selon contrat',
        'Assistance disponible',
        'Gestion simplifiée',
      ],
    },
  },

  about: {
    seo: {
      title: 'À propos de ChanaaCar',
      description:
        "Découvrez la mission, l'expérience et les valeurs de l'agence ChanaaCar.",
    },
    title: 'À propos de ChanaaCar',
    paragraphs: [
      'ChanaaCar est une agence spécialisée dans la location de voitures à Marrakech et dans plusieurs villes du Maroc.',
      "Notre mission est d'offrir à nos clients un service de qualité, des véhicules fiables et une expérience de location simple et transparente.",
      'Depuis notre création, nous accompagnons des voyageurs du monde entier ainsi que des professionnels à la recherche de solutions de mobilité adaptées à leurs besoins.',
    ],
    hero: {
      eyebrow: 'À propos',
      primaryLabel: 'Voir la flotte',
      secondaryLabel: 'Parler à l’équipe',
    },
    narrative: {
      eyebrow: 'L’agence',
      title: 'Une équipe locale qui mise sur la clarté et la disponibilité.',
      intro:
        'Nous accompagnons les voyageurs, familles et professionnels avec un service simple, rapide et lisible.',
    },
    values: {
      title: 'Nos valeurs',
      cards: [
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
      ],
    },
  },

  faq: {
    seo: {
      title: 'FAQ | ChanaaCar',
      description:
        'Questions fréquentes sur les documents, la caution, l’assurance, les annulations et la récupération du véhicule.',
    },
    title: 'Questions fréquentes',
    subtitle:
      'Les réponses essentielles pour comprendre rapidement les conditions de location chez ChanaaCar.',
    hero: {
      eyebrow: 'FAQ',
      primaryLabel: 'Contacter ChanaaCar',
      secondaryLabel: 'Voir les voitures',
    },
    items: [
      {
        question: 'Quels sont vos tarifs de location ?',
        answer:
          'Nos tarifs débutent à 300 DH/jour pour la catégorie économique et varient selon le véhicule, la durée et la saison. Les locations longue durée bénéficient de tarifs dégressifs. Aucun frais caché.',
      },
      {
        question: 'Quel âge minimum et quelle ancienneté de permis faut-il ?',
        answer:
          "Le conducteur doit être âgé d'au moins 21 ans et titulaire d'un permis de conduire valide depuis au moins 2 ans.",
      },
      {
        question: 'Quels documents sont nécessaires pour louer une voiture ?',
        answer:
          "Une pièce d'identité valide (CIN ou passeport), un permis de conduire et un moyen de paiement.",
      },
      {
        question: 'Le kilométrage est-il limité ?',
        answer:
          'La plupart de nos locations incluent un kilométrage adapté à votre itinéraire. Le kilométrage illimité est disponible sur demande selon la catégorie et la durée.',
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
        answer: 'Oui, le montant dépend de la catégorie du véhicule loué.',
      },
      {
        question: 'Puis-je ajouter un conducteur supplémentaire ?',
        answer: 'Oui, sous réserve de présentation des documents nécessaires.',
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
    ],
  },

  contact: {
    seo: {
      title: 'Contact | ChanaaCar',
      description:
        "Coordonnées, disponibilité et formulaire de demande pour contacter l'équipe ChanaaCar.",
    },
    title: 'Contactez ChanaaCar',
    subtitle:
      'Notre équipe est à votre disposition pour répondre à toutes vos questions et vous accompagner dans votre réservation.',
    hero: {
      eyebrow: 'Contact',
      primaryLabel: 'Réserver maintenant',
      secondaryLabel: 'Questions fréquentes',
    },
    groups: [
      { kind: 'phone', label: 'Téléphone', values: contactPhones },
      { kind: 'whatsapp', label: 'WhatsApp', values: contactWhatsapp },
      { kind: 'email', label: 'Email', values: contactEmails },
      { kind: 'address', label: 'Adresse', values: ['Marrakech, Maroc'] },
      {
        kind: 'hours',
        label: 'Horaires',
        values: ['Disponible 7 jours sur 7', 'Assistance 24h/24'],
      },
    ],
  },

  pageCta: {
    title: 'Besoin d’une offre rapide et claire ?',
    description:
      "Consultez notre flotte, contactez l'équipe, ou lancez une réservation avec un parcours plus direct.",
    primaryLabel: 'Découvrir la flotte',
    secondaryLabel: 'Contacter ChanaaCar',
  },

  contactForm: {
    title: 'Envoyer ma demande',
    subtitle:
      'Indiquez-nous vos dates et vos besoins : votre demande s’ouvre dans WhatsApp et notre équipe vous répond rapidement, par téléphone ou sur WhatsApp.',
    labels: {
      nom_complet: 'Nom complet',
      telephone: 'Téléphone',
      email: 'Email',
      date_depart: 'Date de départ',
      date_retour: 'Date de retour',
      lieu_prise_en_charge: 'Lieu de prise en charge',
      lieu_retour: 'Lieu de retour',
      message: 'Message',
    },
    labelSeparator: ' : ',
    submit: 'Envoyer ma demande',
    statusTitle: 'Demande prête dans WhatsApp',
    statusText:
      'Votre demande est prête dans WhatsApp — envoyez-la et nous vous répondrons rapidement.',
    statusLink: 'Ouvrir WhatsApp',
    whatsappIntro: 'Bonjour ChanaaCar, voici ma demande de location :',
    vehiclePrefill:
      'Bonjour ChanaaCar, je souhaite des informations sur la {vehicle}. Est-elle disponible pour mes dates ?',
  },
}

export default fr
