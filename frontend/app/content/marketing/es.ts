import {
  contactEmails,
  contactPhones,
  contactWhatsapp,
  type MarketingContent,
} from './shared'

const es: MarketingContent = {
  dateLocale: 'es-ES',

  heroFeatures: [
    {
      title: 'Precios transparentes',
      description: 'Una oferta clara, condiciones a la vista y una reserva más directa.',
    },
    {
      title: 'Marrakech y aeropuerto',
      description: 'Un servicio pensado para quienes llegan, se van o recorren Marruecos.',
    },
  ],

  marrakech: {
    seo: {
      title: 'Alquiler de coches en Marrakech | ChanaaCar',
      description:
        'Por qué alquilar un coche en Marrakech y cómo ChanaaCar facilita tus desplazamientos por Marruecos.',
    },
    title: 'Alquiler de coches en Marrakech al mejor precio',
    paragraphs: [
      'Marrakech es uno de los destinos más visitados de Marruecos. Alquilar un coche te permite descubrir la ciudad y sus alrededores a tu ritmo.',
      'Tanto si quieres visitar la Medina, Gueliz, la Palmeraie, el Jardín Majorelle o el Atlas como si prefieres escaparte a Esauira, Agadir u Ouarzazate, ChanaaCar pone a tu disposición vehículos para todos los presupuestos.',
    ],
    hero: {
      eyebrow: 'Alquiler en Marrakech',
      primaryLabel: 'Ver los coches',
      secondaryLabel: 'Contáctanos',
    },
    narrative: {
      eyebrow: 'Moverse por Marrakech',
      title: 'Muévete con libertad por la ciudad y sus alrededores.',
      intro:
        'Con un coche encadenas visitas, citas y excursiones con mucha más flexibilidad.',
    },
    reasons: {
      title: '¿Por qué alquilar un coche en Marrakech?',
      items: [
        'Libertad de movimiento',
        'Ahorro de tiempo',
        'Comodidad para toda la familia',
        'Tarifas ventajosas',
        'Acceso fácil a las regiones turísticas',
      ],
    },
  },

  airport: {
    seo: {
      title: 'Alquiler de coches en el aeropuerto Marrakech Menara | ChanaaCar',
      description:
        'Alquiler de coches con entrega rápida del vehículo en el aeropuerto Marrakech Menara.',
    },
    title: 'Alquiler de coches en el aeropuerto Marrakech Menara',
    paragraphs: [
      'Gana tiempo desde que llegas a Marrakech. Nuestro equipo te recibe en el aeropuerto Marrakech Menara y te entrega el vehículo enseguida.',
      'Seguimos la hora de tu vuelo para entregarte el vehículo en las mejores condiciones.',
    ],
    hero: {
      eyebrow: 'Aeropuerto Marrakech Menara',
      primaryLabel: 'Reservar un vehículo',
      secondaryLabel: 'Llamar ahora',
    },
    narrative: {
      eyebrow: 'Llegada a Marrakech',
      title: 'Una recogida más sencilla nada más salir del aeropuerto.',
      intro:
        'En cuanto aterrizas, te ahorras la espera y empiezas tu estancia con un vehículo listo para salir.',
    },
    advantages: {
      title: 'Ventajas',
      items: [
        'Recibimiento en el aeropuerto',
        'Entrega rápida del vehículo',
        'Asistencia si tu vuelo se retrasa',
        'Devolución sencilla antes de tu salida',
      ],
    },
  },

  longTerm: {
    seo: {
      title: 'Alquiler de larga duración en Marruecos | ChanaaCar',
      description:
        'Soluciones de alquiler de larga duración para particulares, profesionales, expatriados y empresas.',
    },
    title: 'Alquiler de larga duración en Marruecos',
    paragraphs: [
      '¿Necesitas un vehículo durante varias semanas o varios meses?',
      'ChanaaCar ofrece soluciones de alquiler de larga duración adaptadas a particulares, profesionales, expatriados y empresas.',
    ],
    hero: {
      eyebrow: 'Larga duración',
      primaryLabel: 'Pedir una oferta',
      secondaryLabel: 'Descubrir la flota',
    },
    narrative: {
      eyebrow: 'Ofertas de larga duración',
      title: 'Una solución más estable para estancias largas y necesidades profesionales.',
      intro:
        'Durante varias semanas o meses, el objetivo es contar con una solución fiable y unas condiciones claras.',
    },
    advantages: {
      title: 'Ventajas',
      items: [
        'Tarifas preferentes',
        'Vehículos recientes',
        'Mantenimiento incluido según contrato',
        'Asistencia disponible',
        'Gestión simplificada',
      ],
    },
  },

  about: {
    seo: {
      title: 'Sobre ChanaaCar',
      description:
        'Descubre la misión, la experiencia y los valores de la agencia ChanaaCar.',
    },
    title: 'Sobre ChanaaCar',
    paragraphs: [
      'ChanaaCar es una agencia especializada en el alquiler de coches en Marrakech y en varias ciudades de Marruecos.',
      'Nuestra misión es ofrecer a nuestros clientes un servicio de calidad, vehículos fiables y una experiencia de alquiler sencilla y transparente.',
      'Desde nuestros inicios, acompañamos a viajeros de todo el mundo y a profesionales que buscan soluciones de movilidad adaptadas a sus necesidades.',
    ],
    hero: {
      eyebrow: 'Sobre nosotros',
      primaryLabel: 'Ver la flota',
      secondaryLabel: 'Hablar con el equipo',
    },
    narrative: {
      eyebrow: 'La agencia',
      title: 'Un equipo local que apuesta por la claridad y la disponibilidad.',
      intro:
        'Acompañamos a viajeros, familias y profesionales con un servicio sencillo, rápido y claro.',
    },
    values: {
      title: 'Nuestros valores',
      cards: [
        {
          title: 'Transparencia',
          description: 'Precios claros y sin sorpresas desagradables.',
        },
        {
          title: 'Calidad',
          description: 'Vehículos recientes y cuidadosamente mantenidos.',
        },
        {
          title: 'Satisfacción del cliente',
          description:
            'Un acompañamiento personalizado antes, durante y después de tu alquiler.',
        },
        {
          title: 'Rapidez',
          description:
            'Un equipo disponible para responder rápidamente a todas tus consultas.',
        },
      ],
    },
  },

  faq: {
    seo: {
      title: 'Preguntas frecuentes | ChanaaCar',
      description:
        'Preguntas frecuentes sobre la documentación, la fianza, el seguro, las cancelaciones y la recogida del vehículo.',
    },
    title: 'Preguntas frecuentes',
    subtitle:
      'Las respuestas esenciales para entender rápidamente las condiciones de alquiler de ChanaaCar.',
    hero: {
      eyebrow: 'FAQ',
      primaryLabel: 'Contactar con ChanaaCar',
      secondaryLabel: 'Ver los coches',
    },
    items: [
      {
        question: '¿Cuánto cuesta alquilar un coche?',
        answer:
          'Las tarifas empiezan en 300 MAD/día para la categoría económica y varían según el vehículo, la duración y la temporada. Los alquileres de larga duración tienen tarifas decrecientes. Sin gastos ocultos.',
      },
      {
        question: '¿Qué edad mínima y qué antigüedad de permiso se necesitan?',
        answer:
          'El conductor debe tener al menos 21 años y un permiso de conducir válido con una antigüedad mínima de 2 años.',
      },
      {
        question: '¿Qué documentos necesito para alquilar un coche?',
        answer:
          'Un documento de identidad válido (DNI o pasaporte), el permiso de conducir y un medio de pago.',
      },
      {
        question: '¿El kilometraje es limitado?',
        answer:
          'La mayoría de nuestros alquileres incluyen un kilometraje adaptado a tu itinerario. El kilometraje ilimitado está disponible bajo petición según la categoría y la duración.',
      },
      {
        question: '¿Puedo recoger el coche en el aeropuerto?',
        answer:
          'Sí. Entregamos y recogemos los vehículos en el aeropuerto Marrakech Menara.',
      },
      {
        question: '¿Está incluido el seguro?',
        answer:
          'Sí, nuestros vehículos están cubiertos según las condiciones del contrato de alquiler.',
      },
      {
        question: '¿Hay que dejar una fianza?',
        answer: 'Sí, el importe depende de la categoría del vehículo alquilado.',
      },
      {
        question: '¿Puedo añadir un conductor adicional?',
        answer: 'Sí, siempre que se presenten los documentos necesarios.',
      },
      {
        question: '¿Qué hago en caso de avería o accidente?',
        answer:
          'Ponte en contacto de inmediato con nuestro equipo de asistencia, disponible las 24 horas.',
      },
      {
        question: '¿Puedo cancelar mi reserva?',
        answer:
          'Sí, según las condiciones de cancelación indicadas al hacer la reserva.',
      },
    ],
  },

  contact: {
    seo: {
      title: 'Contacto | ChanaaCar',
      description:
        'Datos de contacto, disponibilidad y formulario de solicitud para hablar con el equipo de ChanaaCar.',
    },
    title: 'Contacta con ChanaaCar',
    subtitle:
      'Nuestro equipo está a tu disposición para responder a todas tus preguntas y acompañarte en tu reserva.',
    hero: {
      eyebrow: 'Contacto',
      primaryLabel: 'Reservar ahora',
      secondaryLabel: 'Preguntas frecuentes',
    },
    groups: [
      { kind: 'phone', label: 'Teléfono', values: contactPhones },
      { kind: 'whatsapp', label: 'WhatsApp', values: contactWhatsapp },
      { kind: 'email', label: 'Email', values: contactEmails },
      { kind: 'address', label: 'Dirección', values: ['Marrakech, Marruecos'] },
      {
        kind: 'hours',
        label: 'Horario',
        values: ['Abierto los 7 días de la semana', 'Asistencia 24 h'],
      },
    ],
  },

  pageCta: {
    title: '¿Necesitas una oferta rápida y clara?',
    description:
      'Consulta nuestra flota, habla con el equipo o empieza una reserva en pocos pasos.',
    primaryLabel: 'Descubrir la flota',
    secondaryLabel: 'Contactar con ChanaaCar',
  },

  contactForm: {
    title: 'Enviar mi solicitud',
    subtitle:
      'Indícanos tus fechas y lo que necesitas: tu solicitud se abre en WhatsApp y nuestro equipo te responde enseguida, por teléfono o por WhatsApp.',
    labels: {
      nom_complet: 'Nombre completo',
      telephone: 'Teléfono',
      email: 'Email',
      date_depart: 'Fecha de recogida',
      date_retour: 'Fecha de devolución',
      lieu_prise_en_charge: 'Lugar de recogida',
      lieu_retour: 'Lugar de devolución',
      message: 'Mensaje',
    },
    labelSeparator: ': ',
    submit: 'Enviar mi solicitud',
    statusTitle: 'Solicitud lista en WhatsApp',
    statusText:
      'Tu solicitud está lista en WhatsApp: envíala desde allí y te responderemos enseguida.',
    statusLink: 'Abrir WhatsApp',
    whatsappIntro: 'Hola ChanaaCar, esta es mi solicitud de alquiler:',
    vehiclePrefill:
      'Hola ChanaaCar, me gustaría recibir información sobre el {vehicle}. ¿Está disponible en mis fechas?',
  },
}

export default es
