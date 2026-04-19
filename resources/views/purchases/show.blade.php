@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">

        <!-- TITRE -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Détails de l’achat</h2>
            <p class="text-muted mb-0">
                Consultez les informations complètes du bon d’achat ainsi que les produits associés.
            </p>
        </div>

        <!-- CONTENEUR PRINCIPAL -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <!-- HEADER -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold text-primary mb-1">
                            Bon d’achat : {{ $purchase->purchase_code }}
                        </h4>
                        <p class="text-muted mb-0">Visualisez les informations détaillées de cet achat.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('purchases.index') }}"
                            class="btn btn-light rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>

                        <button type="button" class="btn btn-danger rounded-pill px-4" id="save-pdf">
                            <i class="fas fa-file-pdf me-2"></i>Télécharger le PDF
                        </button>
                    </div>
                </div>
                <!-- allert -->

                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($purchase->trashed())
                    <div class="alert alert-danger border-0 shadow-sm rounded-4">
                        Cet achat a été supprimé, mais il reste visible dans l’historique.
                    </div>
                @endif

                <!-- INFORMATIONS -->
                <div class="row g-3 mb-4 ">
                    <div class="col-md-4">
                        <div class="card border-1 bg-light-subtle rounded-4 h-100">
                            <div class="card-body p-3">
                                <div class="text-muted small mb-1">Fournisseur</div>
                                <div class="fw-bold">{{ $purchase->supplier->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-1 bg-light-subtle rounded-4 h-100">
                            <div class="card-body p-3">
                                <div class="text-muted small mb-1">Date d’achat</div>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-1 bg-light-subtle rounded-4 h-100">
                            <div class="card-body p-3">
                                <div class="text-muted small mb-1">Statut</div>
                                <div>
                                    @if ($purchase->status == 'reçu')
                                        <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                            Reçu
                                        </span>
                                    @elseif($purchase->status == 'annulé')
                                        <span class="badge rounded-pill px-3 py-2 bg-danger-subtle text-danger">
                                            Annulé
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 bg-warning-subtle text-warning-emphasis">
                                            En attente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- TABLEAU -->
                <div class="table-responsive purchase-details-table">
                    <table class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Référence</th>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix d’achat</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody id="purchase-items-list">
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td class="product-Referonce fw-semibold text-primary">
                                        {{ $item->product->Referonce ?? '-' }}
                                    </td>
                                    <td class="product-designation">
                                        {{ $item->product->Designation ?? '-' }}
                                    </td>
                                    <td class="product-quantity fw-semibold">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="product-price">
                                        {{ number_format($item->buy_price, 2, '.', '') }}
                                    </td>
                                    <td class="product-total fw-semibold text-success">
                                        {{ number_format($item->quantity * $item->buy_price, 2, '.', '') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL GENERAL -->
                <div class="d-flex justify-content-end mt-4">
                    <div class="card border-0 shadow-sm rounded-4" style="min-width: 280px;">
                        <div class="card-body text-end">
                            <div class="text-muted small">Total général</div>
                            <div class="fs-4 fw-bold text-success">
                                {{ number_format($purchase->total, 2) }} MAD
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    @php
        $supplierName = optional($purchase->supplier)->name ?: 'Fournisseur';
        $grandTotal = $purchase->total;
        $formattedDate = \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y');
    @endphp


    <style>
        .purchase-details-table {
            overflow: hidden;
            background: #ffffff;
        }

        .purchase-details-table table th,
        .purchase-details-table table td {
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .purchase-details-table tbody tr {
            transition: all 0.2s ease;
        }

        .purchase-details-table tbody tr:hover {
            background: #f8fafc;
        }

        .bg-light-subtle {
            background: #f8fafc;
        }

        .bg-success-subtle {
            background: #ecfdf5;
        }

        .bg-danger-subtle {
            background: #fee2e2;
        }

        .bg-warning-subtle {
            background: #fef3c7;
        }
    </style>

    <script>
        document.getElementById('save-pdf').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();

            const purchaseCode = @json($purchase->purchase_code);
            const purchaseDate = @json($formattedDate);
            const supplierName = @json($supplierName);
            const supplierAddress = @json($purchase->supplier->address ?? '');
            const grandTotal = @json($purchase->total);

            // إذا عندك company بحال facture استعملها
            const logoUrl = @json(!empty($company?->logo) ? asset('storage/' . $company->logo) : asset('images/img.jpg'));
            const companyName = @json($company->company_name ?? '');
            const companyCity = @json($company->city ?? 'Agader');
            const companyAddress = @json($company->address ?? '');
            const companyPhone = @json($company->phone ?? '0661247414');
            const companyEmail = @json($company->email ?? '');
            const footerNote = @json(
                $company->footer_note ??
                    'Notre société est spécialisée dans la vente de pièces pour machines industrielles. Nous nous engageons à fournir des produits de qualité, fiables et adaptés à vos besoins. Nous vous remercions pour votre confiance.');
            const footerContact = @json($company->footer_contact ?? 'Tell: 0661247414  || 0680661043');

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = logoUrl;

            img.onload = function() {
                // =========================
                // LOGO FULL WIDTH
                // =========================
                const logoWidth = pageWidth;
                const logoHeight = 35;
                doc.addImage(img, 'PNG', 0, 0, logoWidth, logoHeight);

                // =========================
                // NOM SOCIETE
                // =========================
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(16);
                doc.setTextColor(70, 85, 110);
                doc.text((companyName || '').toUpperCase(), pageWidth / 2, 42, {
                    align: 'center'
                });

                doc.setDrawColor(0, 102, 204);
                doc.setLineWidth(0.4);
                doc.line(65, 46, pageWidth - 65, 46);

                // =========================
                // HEADER
                // =========================
                doc.setTextColor(0, 0, 0);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                doc.text(`BON D'ACHAT N° ${purchaseCode}`, 12, 56);

                doc.setFont('helvetica', 'normal');
                doc.text(`${companyCity}, le : ${purchaseDate}`, pageWidth - 19, 56, {
                    align: 'right'
                });

                // =========================
                // FOURNISSEUR BOX
                // =========================
                doc.setDrawColor(170, 170, 170);
                doc.setLineWidth(0.25);
                doc.roundedRect(120, 58, 72, 24, 3, 3);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(9);
                doc.setTextColor(90, 100, 125);

                const labelX = 124;
                const labelY = 64;

                doc.text('FOURNISSEUR :', labelX, labelY);

                const labelWidth = doc.getTextWidth('FOURNISSEUR :');
                const nameX = labelX + labelWidth + 3;

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.setTextColor(0, 0, 0);
                doc.text(String(supplierName).toUpperCase(), nameX, 64, {
                    maxWidth: 42
                });
                // 🔹 CLIENT ADDRESS
                if (supplierAddress) {
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(8);

                    const addressLines = doc.splitTextToSize(supplierAddress, 50);

                    doc.text(addressLines, nameX, 72);
                }

                // =========================
                // TABLE DATA
                // =========================
                const rows = [];
                document.querySelectorAll('#purchase-items-list tr').forEach(row => {
                    const referonce = row.querySelector('.product-Referonce')?.textContent?.trim() ||
                    '';
                    const designation = row.querySelector('.product-designation')?.textContent
                    ?.trim() || '';
                    const quantity = row.querySelector('.product-quantity')?.textContent?.trim() || '1';
                    const price = row.querySelector('.product-price')?.textContent?.trim() || '0.00';
                    const total = row.querySelector('.product-total')?.textContent?.trim() || '0.00';

                    if (referonce || designation) {
                        rows.push([
                            referonce,
                            designation,
                            quantity.replace('.', ','),
                            price.replace('.', ','),
                            total.replace('.', ',')
                        ]);
                    }
                });

                while (rows.length < 15) {
                    rows.push(['', '', '', '', '']);
                }

                doc.autoTable({
                    startY: 84,
                    head: [
                        [
                            'Référence',
                            'Désignation',
                            'Quantité',
                            'P.U Achat',
                            'Montant'
                        ]
                    ],
                    body: rows,
                    theme: 'plain',
                    styles: {
                        font: 'helvetica',
                        fontSize: 9,
                        textColor: [0, 0, 0],
                        cellPadding: 3,
                        overflow: 'linebreak',
                        valign: 'middle',
                        lineWidth: 0
                    },
                    headStyles: {
                        fontStyle: 'bold',
                        halign: 'center',
                        textColor: [70, 85, 110],
                        fillColor: false,
                        lineWidth: 0
                    },
                    columnStyles: {
                        0: {
                            cellWidth: 28,
                            halign: 'left'
                        },
                        1: {
                            cellWidth: 80,
                            halign: 'left'
                        },
                        2: {
                            cellWidth: 22,
                            halign: 'center'
                        },
                        3: {
                            cellWidth: 24,
                            halign: 'center'
                        },
                        4: {
                            cellWidth: 26,
                            halign: 'center'
                        }
                    },
                    margin: {
                        left: 12,
                        right: 12
                    },
                    didDrawPage: function(data) {
                        const x = data.settings.margin.left;
                        const y = data.settings.startY;
                        const tableWidth = 180;
                        const rowHeight = 9;
                        const totalHeight = rowHeight * (rows.length + 1);

                        doc.setDrawColor(170, 170, 170);
                        doc.setLineWidth(0.2);
                        doc.rect(x, y, tableWidth, totalHeight);

                        const col1 = x + 28;
                        const col2 = x + 108;
                        const col3 = x + 130;
                        const col4 = x + 154;

                        doc.line(col1, y, col1, y + totalHeight);
                        doc.line(col2, y, col2, y + totalHeight);
                        doc.line(col3, y, col3, y + totalHeight);
                        doc.line(col4, y, col4, y + totalHeight);

                        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
                    }
                });

                // =========================
                // TOTAL
                // =========================
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                // قياسات table نفسها
                const tableX = 12;
                const tableWidth = 180;
                const tableRight = tableX + tableWidth;

                // قياسات total box
                const totalLabelW = 28;
                const totalAmountW = 34;
                const totalH = 10;
                const totalGroupW = totalLabelW + totalAmountW;

                // TOTAL خاصها تلصق فاليمين ديال table
                const totalX = tableRight - totalGroupW;

                // TOTAL خاصها تلصق مباشرة تحت table
                const totalY = (doc.lastAutoTable.finalY || 200) - 0.5;

                // label TOTAL
                doc.setFillColor(0, 102, 204);
                doc.setTextColor(255, 255, 255);
                doc.rect(totalX, totalY, totalLabelW, totalH, 'F');
                doc.text('TOTAL', totalX + (totalLabelW / 2), totalY + 6.5, {
                    align: 'center'
                });

                // amount box
                doc.setFillColor(255, 255, 255);
                doc.setTextColor(0, 0, 0);
                doc.rect(totalX + totalLabelW, totalY, totalAmountW, totalH);

                // format total
                const numberValue = Number(grandTotal) || 0;
                const parts = numberValue.toFixed(2).split('.');
                const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                const totalText = integerPart + ',' + parts[1];

                // text amount
                doc.text(totalText, totalX + totalLabelW + totalAmountW - 2, totalY + 6.5, {
                    align: 'right'
                });
                // =========================
                // FOOTER
                // =========================
                let footerY = pageHeight - 22;

                doc.setDrawColor(200, 200, 200);
                doc.line(12, footerY - 6, pageWidth - 12, footerY - 6);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(7.5);
                doc.setTextColor(90, 90, 90);
                doc.text(
                    footerNote || "DOCUMENT D'ACHAT - SYSTÈME ERP",
                    pageWidth / 2,
                    footerY - 1, {
                        align: 'center',
                        maxWidth: 180
                    }
                );

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7.5);

                if (companyAddress) {
                    doc.text(companyAddress, pageWidth / 2, footerY + 7, {
                        align: 'center'
                    });
                }

                doc.text(
                    footerContact || `${companyPhone} - ${companyEmail}`,
                    pageWidth / 2,
                    footerY + 12, {
                        align: 'center'
                    }
                );

                const printDate = new Date().toLocaleString('fr-FR');

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7);
                doc.setTextColor(120, 120, 120);

                doc.text(`Imprimé le : ${printDate}`, 12, pageHeight - 5);

                doc.save(`achat_${purchaseCode}.pdf`);
            };

            img.onerror = function() {
                alert("Erreur lors du chargement du logo.");
            };
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
@endsection
