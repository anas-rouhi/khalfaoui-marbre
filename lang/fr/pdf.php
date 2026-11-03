<?php

// Libellés du devis PDF (resources/views/pdf/devis.blade.php).

return [
    'label' => 'Devis estimatif',
    'title' => 'Offre de prix',
    'reference' => 'Référence',
    'date' => 'Date',
    'validity' => 'Validité',
    'validity_value' => '30 jours',
    'phone' => 'Tél / WhatsApp',
    'client' => 'Client',
    'site' => 'Chantier',
    'material_tbd' => 'Matériau à définir',
    'designation' => 'Désignation',
    'unit_price' => 'Prix unitaire',
    'quantity' => 'Quantité',
    'amount' => 'Montant',
    'supply' => 'Fourniture',
    'material' => 'Matériau',
    'origin' => 'Origine',
    'installation' => 'Pose et finitions',
    'installation_detail' => 'main d\'œuvre, découpes et chants',
    'total' => 'Total estimé',
    'client_notes' => 'Précisions du client',
    'conditions' => 'Conditions',
    'terms' => [
        'Devis estimatif établi à partir des informations communiquées en ligne. Le prix définitif est arrêté après métré contradictoire sur site.',
        'Montants exprimés en dirhams, toutes taxes comprises.',
        'Prix hors transport, dépose de l\'existant, travaux de maçonnerie et raccordements de plomberie ou d\'électricité.',
        'La pierre naturelle est un matériau vivant : le veinage et la teinte varient d\'un bloc à l\'autre. Les tranches sont validées avec le client avant découpe.',
        'Offre valable 30 jours à compter de la date d\'émission, sous réserve de disponibilité en stock.',
        'Délai indicatif de réalisation : 2 à 5 semaines selon la nature du chantier.',
    ],
    // Encadré de bas de page : le bon geste d'entretien évite les litiges.
    'care_title' => 'Entretien de votre pierre naturelle',
    'care_tips' => [
        'Nettoyez à l\'eau tiède et au savon neutre (pH neutre), puis séchez avec un chiffon doux.',
        'Proscrivez produits acides, anticalcaires, javel, vinaigre et citron : ils ternissent et rongent la pierre de façon irréversible.',
        'Essuyez sans attendre café, huile, vin ou jus, et renouvelez le traitement hydrofuge tous les 12 à 18 mois.',
    ],
    'care_note' => 'Les altérations dues à un produit ou à un entretien inadapté ne relèvent pas de notre responsabilité.',
    'next_step' => 'Passons à l\'étape suivante',
    'next_step_detail' => 'Nous convenons d\'un métré gratuit, chez vous, sous 48 heures.',
    'currency' => 'DH',
    'unit' => 'm²',
];
