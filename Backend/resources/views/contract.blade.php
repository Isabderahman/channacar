<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
@php
    $r = $reservation;
    $client = $r->client;
    $sd = $r->second_driver ?? [];
    $d = $details ?? [];
    $fuelScale = fn ($selected) => collect(['0', '1/4', '1/2', '3/4', 'Plein'])
        ->map(fn ($lvl) => $selected === $lvl ? '<b>['.$lvl.']</b>' : $lvl)
        ->implode(' &nbsp; ');
    $franchise = array_key_exists('suppression_franchise', $d) ? (bool) $d['suppression_franchise'] : ((float) $r->insurance_total > 0);
    $val = fn ($v) => ($v === null || $v === '') ? '________________' : $v;
    $date = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '____________';
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; }
    .wrap { padding: 18px 24px; }
    table { border-collapse: collapse; width: 100%; }
    .head { width: 100%; margin-bottom: 6px; }
    .brand { font-size: 26px; font-weight: bold; letter-spacing: 2px; color: #173a8a; }
    .brand small { display: block; font-size: 9px; letter-spacing: 3px; color: #173a8a; }
    .title { font-size: 20px; font-weight: bold; letter-spacing: 1px; text-align: right; }
    .contract-no { text-align: right; font-size: 13px; font-weight: bold; }
    .contract-no b { color: #c0392b; font-size: 16px; }
    .meta td { border: 1px solid #173a8a; padding: 4px 6px; font-style: italic; }
    .section-title { text-align: center; font-style: italic; font-weight: bold; font-size: 15px; margin: 8px 0 4px; }
    .box { border: 1px solid #173a8a; padding: 6px 8px; }
    .col-title { text-align: center; font-style: italic; font-weight: bold; font-size: 12px; margin-bottom: 4px; }
    .field { padding: 2px 0; border-bottom: 1px dotted #999; }
    .label { font-weight: bold; }
    .grid td { vertical-align: top; }
    .lined td, .lined th { border: 1px solid #173a8a; padding: 4px 6px; }
    .lined th { background: #f0f3fa; font-style: italic; }
    .prix td { padding: 3px 0; border-bottom: 1px dotted #aaa; }
    .prix .amt { text-align: right; font-weight: bold; }
    .total { border: 1px solid #173a8a; padding: 5px 8px; font-weight: bold; font-size: 12px; }
    .check { border: 1px solid #173a8a; display: inline-block; width: 42px; text-align: center; padding: 2px 0; }
    .muted { color: #555; }
    .foot { margin-top: 10px; border-top: 2px solid #173a8a; padding-top: 6px; text-align: center; font-size: 9px; }
    .sig { height: 46px; }
    .annex img { max-width: 100%; max-height: 320px; border: 1px solid #ccc; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="wrap">
    <table class="head">
        <tr>
            <td style="width:40%"><div class="brand">CHANÂA CAR<small>CAR RENTAL SERVICE</small></div></td>
            <td style="width:60%">
                <div class="title">CONTRAT DE LOCATION</div>
                <div class="contract-no">Contrat N&ordm; <b>{{ $r->contract_number }}</b></div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td style="width:50%"><span class="label">Marque :</span> {{ $r->car ? $r->car->brand.' '.$r->car->name : '' }}</td>
            <td style="width:50%"><span class="label">N&deg; d'Immatriculation :</span> {{ $r->car?->registration_number }}</td>
        </tr>
    </table>

    <div class="section-title">Locataire</div>
    <table class="grid">
        <tr>
            <td style="width:50%; padding-right:8px;">
                <div class="box">
                    <div class="col-title">1er Conducteur</div>
                    <div class="field"><span class="label">Nom &amp; Pr&eacute;nom :</span> {{ $val($client->full_name ?? null) }}</div>
                    <div class="field"><span class="label">N&eacute; le :</span> {{ $date($client->birth_date ?? null) }} <span class="label">&agrave;</span> {{ $val($client->birth_place ?? null) }}</div>
                    <div class="field"><span class="label">Adresse :</span> {{ $val($client->address ?? null) }}</div>
                    <div class="field"><span class="label">T&eacute;l&eacute;phone :</span> {{ $val($client->phone ?? null) }}</div>
                    <div class="field"><span class="label">Permis de Conduire n&deg; :</span> {{ $val($client->driver_license ?? null) }}</div>
                    <div class="field"><span class="label">D&eacute;livr&eacute; le :</span> {{ $date($client->license_issued_at ?? null) }} <span class="label">&agrave;</span> {{ $val($client->license_issued_place ?? null) }}</div>
                    <div class="field"><span class="label">Passeport n&deg; :</span> {{ $val($client->passport_number ?? null) }}</div>
                    <div class="field"><span class="label">CIN n&deg; :</span> {{ $val($client->cin_number ?? null) }}</div>
                </div>
            </td>
            <td style="width:50%;">
                <div class="box">
                    <div class="col-title">2&egrave;me Conducteur</div>
                    <div class="field"><span class="label">Nom &amp; Pr&eacute;nom :</span> {{ $val($sd['full_name'] ?? null) }}</div>
                    <div class="field"><span class="label">N&eacute; le :</span> {{ $date($sd['birth_date'] ?? null) }} <span class="label">&agrave;</span> {{ $val($sd['birth_place'] ?? null) }}</div>
                    <div class="field"><span class="label">Adresse :</span> {{ $val($sd['address'] ?? null) }}</div>
                    <div class="field"><span class="label">T&eacute;l&eacute;phone :</span> {{ $val($sd['phone'] ?? null) }}</div>
                    <div class="field"><span class="label">Permis de Conduire n&deg; :</span> {{ $val($sd['driver_license'] ?? null) }}</div>
                    <div class="field"><span class="label">D&eacute;livr&eacute; le :</span> {{ $date($sd['license_issued_at'] ?? null) }} <span class="label">&agrave;</span> {{ $val($sd['license_issued_place'] ?? null) }}</div>
                    <div class="field"><span class="label">Passeport n&deg; :</span> {{ $val($sd['passport_number'] ?? null) }}</div>
                    <div class="field"><span class="label">CIN n&deg; :</span> {{ $val($sd['cin_number'] ?? null) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="grid" style="margin-top:8px;">
        <tr>
            <td style="width:55%; padding-right:8px; vertical-align:top;">
                <table class="lined">
                    <tr><th></th><th>Date</th><th>Heure</th><th>Lieu</th></tr>
                    <tr>
                        <td class="label">D&eacute;part</td>
                        <td>{{ $pickupAt->format('d/m/Y') }}</td>
                        <td>{{ $pickupAt->format('H:i') }}</td>
                        <td>{{ $r->pickupLocation?->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Retour</td>
                        <td>{{ $dropoffAt->format('d/m/Y') }}</td>
                        <td>{{ $dropoffAt->format('H:i') }}</td>
                        <td>{{ $r->dropoffLocation?->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Prolongation</td>
                        <td>{{ !empty($d['prolongation_date']) ? \Illuminate\Support\Carbon::parse($d['prolongation_date'])->format('d/m/Y') : '' }}</td>
                        <td>{{ $d['prolongation_time'] ?? '' }}</td>
                        <td>{{ $d['prolongation_location'] ?? '' }}</td>
                    </tr>
                </table>

                <div class="label" style="margin:8px 0 4px; text-decoration:underline;">Prix</div>
                <table class="prix">
                    <tr><td>{{ $days }} Jour(s) &agrave; {{ $money($dailyRate) }} Dh</td><td class="amt">{{ $money($carNet) }} Dh</td></tr>
                    <tr><td>Semaine(s) &agrave; ____________ Dh</td><td class="amt"></td></tr>
                    <tr><td>Net location</td><td class="amt">{{ $money($carNet) }} Dh</td></tr>
                    <tr><td>Divers (options / assurance){{ !empty($d['divers_note']) ? ' — '.$d['divers_note'] : '' }}</td><td class="amt">{{ $money($divers) }} Dh</td></tr>
                    <tr><td>Frais de Livraison/Reprise</td><td class="amt">{{ $money($delivery) }} Dh</td></tr>
                    <tr><td>TVA 20% (incluse)</td><td class="amt">{{ $money($tva) }} Dh</td></tr>
                </table>
                <div class="total" style="margin-top:6px;">
                    <table><tr><td>Total G&eacute;n&eacute;ral</td><td style="text-align:right;">{{ $money($total) }} Dh</td></tr></table>
                </div>
                <div style="margin-top:6px;">
                    <span class="label">Mode R&egrave;glement :</span>
                    <span class="check">Cash {{ $r->payment_method === 'cash' ? 'X' : '' }}</span>
                    <span class="check">Ch&egrave;que {{ $r->payment_method === 'cheque' ? 'X' : '' }}</span>
                </div>
            </td>
            <td style="width:45%; vertical-align:top;">
                <div class="box">
                    <div class="col-title">Situation du V&eacute;hicule</div>
                    <table style="width:100%; text-align:center;">
                        <tr>
                            <td style="width:50%; font-size:9px;" class="label">D&Eacute;PART / OUT</td>
                            <td style="width:50%; font-size:9px;" class="label">RETOUR / IN</td>
                        </tr>
                        <tr>
                            <td style="height:60px; border:1px solid #ccc; font-size:8px; vertical-align:top; padding:3px;">{{ $d['condition_depart'] ?? '' }}</td>
                            <td style="height:60px; border:1px solid #ccc; font-size:8px; vertical-align:top; padding:3px;">{{ $d['condition_retour'] ?? '' }}</td>
                        </tr>
                    </table>
                    <div style="margin-top:6px; font-size:9px;">Carburant d&eacute;part : {!! $fuelScale($d['fuel_depart'] ?? null) !!}</div>
                    <div style="margin-top:2px; font-size:9px;">Carburant retour : {!! $fuelScale($d['fuel_retour'] ?? null) !!}</div>
                </div>

                <div class="box" style="margin-top:8px;">
                    <div class="label">Assurances</div>
                    <div class="field">Suppression de Franchise :
                        <span class="check">OUI {{ $franchise ? 'X' : '' }}</span>
                        <span class="check">NON {{ $franchise ? '' : 'X' }}</span>
                    </div>
                    <div class="field">Personnes Transport&eacute;es : {{ $d['personnes_transportees'] ?? ($r->car?->seats ?? '') }}</div>
                    <div class="field"><span class="label">Km d&eacute;part :</span> {{ $d['km_depart'] ?? '________________' }}</div>
                    <div class="field"><span class="label">Km arriv&eacute;e :</span> {{ $d['km_arrivee'] ?? '________________' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="margin-top:14px;">
        <tr>
            <td style="width:33%;"><div class="label">Fait le : {{ now()->format('d/m/Y') }}</div></td>
            <td style="width:34%; text-align:center;"><div class="label">Le Locataire</div><div class="sig"></div></td>
            <td style="width:33%; text-align:center;"><div class="label">2&egrave;me Conducteur</div><div class="sig"></div></td>
        </tr>
    </table>

    <div class="muted" style="font-size:8px; margin-top:4px;">
        Le client est seul responsable des violations du code de la route. Je reconnais avoir pris connaissance des conditions g&eacute;n&eacute;rales de location au verso et accept&eacute; de m'y conformer.
    </div>

    <div class="foot">
        Magazine 2 N&deg;534 Afaq 2 Saad, Marrakech &mdash; Fix : +212 6 66 62 36 45<br>
        Email : chanaacar@gmail.com &mdash; T&eacute;l : +212 6 61 23 29 02 / +212 6 15 93 39 36
    </div>

    <div class="page-break"></div>
    <h2 style="text-align:center; font-size:15px; margin:0 0 8px;">CONDITIONS GENERALES DE LOCATION</h2>
    <table class="conditions" style="font-size:6.6px; line-height:1.3; text-align:justify;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:8px;">
                <p><b>Art.&nbsp;1 - UTILISATION DU VEHICULE</b><br>
                - La location est personnelle et elle n'est en aucun cas transmissible. Le locataire s'engage à ne pas laisser conduire la voiture par d'autres personnes que celles ci-contre agréées par le loueur lequel devient entièrement responsable du véhicule dès que celui-ci a été pris en charge.<br>
                - Le locataire s'interdit de participer à tout rallye, course, concours, ou toute autre compétition de quelque nature que ce soit, ainsi qu'à des essais ou préparations. Il s'interdit également de circuler en dehors des voies carrossables et d'utiliser des pistes non goudronnées ; les dégâts causés par suite de cette utilisation seront facturés au client qui s'oblige à les régler.<br>
                - Le locataire s'engage à ne pas utiliser le véhicule à des fins illicites ou au transport de personnes à titre onéreux et à ne pas l'emmener hors du Territoire Marocain.<br>
                - Il s'engage également à ne pas atteler de remorque ou véhicule similaire, à n'apporter aucune modification au véhicule, à ne laisser en aucun cas les titres de circulation dans celui-ci, à utiliser à chaque arrêt les systèmes de fermeture et de protection.</p>

                <p><b>Art.&nbsp;2 - ETAT DU VEHICULE</b><br>
                - Le véhicule est livré au locataire en parfait état de marche et de carrosserie avec les accessoires normaux ; les compteurs sont plombés et les plombs ne pourront être enlevés ou violés sous peine de payer une distance de 150 km par jour de location. Le véhicule sera rendu dans le même état qu'à son départ, à défaut le locataire devra acquitter le montant de la remise en état. Les kilomètres facturés sont ceux indiqués par le compteur. Les cinq pneus sont au départ en bon état ; en cas de détérioration de l'un d'entre eux pour une cause autre que l'usage normal, le locataire s'engage à le remplacer immédiatement par un de mêmes dimensions, de même marque et à usage sensiblement égal.</p>

                <p><b>Art.&nbsp;3 - CARBURANT &amp; LUBRIFIANTS</b><br>
                - La fourniture de carburant est à la charge du locataire ; celui-ci doit vérifier en permanence le niveau d'huile et d'eau, et effectuer aux intervalles indiqués par le constructeur le graissage (y compris la vérification des niveaux de la boîte de vitesse et du pont) et la vidange du moteur. Si la voiture est livrée neuve, le locataire s'engage à faire effectuer les révisions obligatoires par un agent officiel de la marque ; celles-ci seront remboursées sur justificatifs.</p>

                <p><b>Art.&nbsp;4 - ENTRETIEN ET REPARATIONS</b><br>
                - Les réparations et rechanges de pièces ou de pneumatiques résultant de l'usure normale, de négligence ou de cause accidentelle demeurent à la charge du locataire et seront effectuées sans délai par le loueur ; leur montant sera augmenté de l'indemnité d'immobilisation prévue à l'art.&nbsp;8. Si le véhicule est immobilisé hors de la ville de Marrakech, le locataire peut charger de ces travaux un agent officiel de la marque après accord écrit du loueur et doit se faire remettre une facture acquittée détaillée ainsi que les pièces défectueuses remplacées.</p>

                <p><b>Art.&nbsp;5 - ASSURANCES</b><br>
                - Sous réserve de l'exécution de ses obligations, le locataire est garanti : a) contre les conséquences pécuniaires de sa responsabilité civile à raison des accidents causés aux tiers dans les limites fixées par le code des assurances ; b) contre le vol et l'incendie du véhicule, sous déduction de la franchise prévue au tarif ; c) en cas d'accident, le locataire est astreint de régler la franchise non rachetable arrêtée à 10% de la valeur globale du véhicule. Est exclu de la garantie tout accident survenant aux objets ou marchandises transportés. Il n'y a pas d'assurance pour tout conducteur non muni d'un permis de conduire en état de validité depuis un an au moins. Le locataire s'engage à déclarer au loueur dans les 24 heures et immédiatement aux autorités tout accident, vol ou incendie même partiel, sous peine d'être déchu du bénéfice de l'assurance.</p>
            </td>
            <td style="width:50%; vertical-align:top; padding-left:8px;">
                <p><b>Art.&nbsp;6 - REGLEMENT, PREPAIEMENT, PROLONGATION &amp; RETOUR</b><br>
                - Les montants de la location et du prépaiement sont déterminés par les tarifs en vigueur et payables d'avance. Le prépaiement ne pourra servir à une prolongation. Si le locataire souhaite conserver la voiture pour un temps supérieur à celui indiqué, il devra, après accord du loueur, faire parvenir le montant de la période supplémentaire avant l'expiration de la location en cours, sous peine de poursuites pour détournement de véhicule et abus de confiance.<br>
                - Le retour de la voiture devra être effectué pendant les heures ouvrables ; le loueur n'est pas responsable des objets laissés dans la voiture. Le locataire s'interdit d'abandonner le véhicule sans accord écrit du loueur ; à défaut, la voiture sera rapatriée à ses frais, la location continuant à courir jusqu'au retour.<br>
                - En fin de location, le règlement du solde doit intervenir au plus tard dans les 24 heures, faute de quoi une indemnité fixée forfaitairement à 20% des sommes restant dues sera appliquée.</p>

                <p><b>Art.&nbsp;7 - ANNULATION DE LA RESERVATION</b><br>
                - En cas d'annulation, le locataire renonce à 25% du montant global de la location.</p>

                <p><b>Art.&nbsp;8 - DOCUMENTS DE LA VOITURE</b><br>
                - Le locataire remet au loueur, dès le retour de la voiture, tous les titres de circulation afférents ; faute de quoi la location continuera de lui être facturée au prix initial jusqu'à production d'un certificat de perte et règlement des frais de duplicata.</p>

                <p><b>Art.&nbsp;9 - RESPONSABILITE</b><br>
                - Le locataire demeure seul responsable des amendes, contraventions, procès-verbaux, poursuites douanières et autres imprévus établis contre lui ; il s'engage à rembourser au loueur tous frais de cette nature éventuellement payés en ses lieu et place.</p>

                <p><b>Art.&nbsp;10 - JURIDICTION</b><br>
                - En cas de contestation relative à l'exécution du présent contrat, il est fait attribution de juridiction à Marrakech ; le tribunal compétent sera, au choix du demandeur, celui du lieu où demeure le locataire ou celui du lieu de la signature du contrat.</p>

                <p style="margin-top:18px;">
                    <b>Lu et approuvé,</b><br><br>
                    Le Locataire : ____________________________
                </p>
            </td>
        </tr>
    </table>

    @if ($driverLicenseImage || $driverLicenseVersoImage || $identityImage || $identityVersoImage)
        <div class="page-break"></div>
        <div class="section-title">Documents du Locataire</div>
        <table class="annex">
            <tr>
                <td colspan="2" class="label" style="padding:6px 6px 2px;">Permis de Conduire</td>
            </tr>
            <tr>
                <td style="width:50%; text-align:center; padding:6px;">
                    <div class="muted" style="font-size:8px;">Recto</div>
                    @if ($driverLicenseImage)<img src="{{ $driverLicenseImage }}" alt="">@else<div class="muted">Non fourni</div>@endif
                </td>
                <td style="width:50%; text-align:center; padding:6px;">
                    <div class="muted" style="font-size:8px;">Verso</div>
                    @if ($driverLicenseVersoImage)<img src="{{ $driverLicenseVersoImage }}" alt="">@else<div class="muted">Non fourni</div>@endif
                </td>
            </tr>
            <tr>
                <td colspan="2" class="label" style="padding:10px 6px 2px;">CIN / Passeport</td>
            </tr>
            <tr>
                <td style="width:50%; text-align:center; padding:6px;">
                    <div class="muted" style="font-size:8px;">Recto</div>
                    @if ($identityImage)<img src="{{ $identityImage }}" alt="">@else<div class="muted">Non fourni</div>@endif
                </td>
                <td style="width:50%; text-align:center; padding:6px;">
                    <div class="muted" style="font-size:8px;">Verso</div>
                    @if ($identityVersoImage)<img src="{{ $identityVersoImage }}" alt="">@else<div class="muted">Non fourni</div>@endif
                </td>
            </tr>
        </table>
    @endif
</div>
</body>
</html>
