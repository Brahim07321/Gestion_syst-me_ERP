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
                        <a href="{{ route('purchases.index') }}" class="btn btn-light rounded-pill px-4">
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
        $grandTotal = number_format($purchase->total, 2, '.', '');
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
            const grandTotal = @json($grandTotal);

            const logoUrl = '{{ asset('images/img.png') }}';
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = logoUrl;

            img.onload = function() {

                const logoWidth = 85;
                const logoHeight = 28;
                const logoX = (pageWidth - logoWidth) / 2;
                const logoY = 8;

                doc.addImage(img, 'PNG', logoX, logoY, logoWidth, logoHeight);

                doc.setDrawColor(120, 120, 120);
                doc.setLineWidth(0.3);
                doc.line(12, 40, pageWidth - 12, 40);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.text(`BON D'ACHAT N° ${purchaseCode}`, 12, 48);
                doc.text(`FOURNISSEUR`, 12, 55);

                doc.text(`Marrakech, le : ${purchaseDate}`, pageWidth - 17, 44, {
                    align: 'right'
                });

                doc.rect(122, 46, 70, 20);
                doc.text(String(supplierName).toUpperCase(), 157, 60, {
                    align: 'center'
                });

                const rows = [];
                document.querySelectorAll('#purchase-items-list tr').forEach(row => {
                    const designation = row.querySelector('.product-designation')?.textContent
                        ?.trim() || '';
                    const Referonce = row.querySelector('.product-Referonce')?.textContent?.trim() ||
                        '';
                    const quantity = row.querySelector('.product-quantity')?.textContent?.trim() || '1';
                    const price = row.querySelector('.product-price')?.textContent?.trim() || '0.00';
                    const total = row.querySelector('.product-total')?.textContent?.trim() || '0.00';

                    if (designation || Referonce) {
                        rows.push([
                            Referonce,
                            designation,
                            quantity.replace('.', ','),
                            price.replace('.', ','),
                            total.replace('.', ',')
                        ]);
                    }
                });

                const minRows = 15;
                while (rows.length < minRows) {
                    rows.push(['', '', '', '', '']);
                }

                doc.autoTable({
                    startY: 70,
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
                        cellPadding: 2,
                        overflow: 'linebreak',
                        valign: 'middle',
                        lineWidth: 0
                    },
                    headStyles: {
                        fontStyle: 'bold',
                        halign: 'center',
                        textColor: [0, 0, 0],
                        fillColor: false,
                        lineWidth: 0
                    },
                    columnStyles: {
                        0: {
                            cellWidth: 30,
                            halign: 'left'
                        },
                        1: {
                            cellWidth: 62,
                            halign: 'left'
                        },
                        2: {
                            cellWidth: 22,
                            halign: 'center'
                        },
                        3: {
                            cellWidth: 28,
                            halign: 'center'
                        },
                        4: {
                            cellWidth: 38,
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
                        const rowHeight = 8;

                        doc.setDrawColor(120, 120, 120);
                        doc.setLineWidth(0.2);

                        const bodyRowsCount = rows.length > 0 ? rows.length : 1;
                        const totalHeight = rowHeight * (bodyRowsCount + 1);

                        doc.rect(x, y, tableWidth, totalHeight);

                        const col1 = x + 30;
                        const col2 = x + 92;
                        const col3 = x + 114;
                        const col4 = x + 142;

                        doc.line(col1, y, col1, y + totalHeight);
                        doc.line(col2, y, col2, y + totalHeight);
                        doc.line(col3, y, col3, y + totalHeight);
                        doc.line(col4, y, col4, y + totalHeight);

                        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
                    }
                });

                const totalY = 200;

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                doc.setFillColor(0, 102, 204);
                doc.rect(142, totalY, 28, 10, 'F');
                doc.setTextColor(255, 255, 255);
                doc.text('TOTAL', 156, totalY + 6.5, {
                    align: 'center'
                });

                doc.setFillColor(255, 255, 255);
                doc.setTextColor(0, 0, 0);
                doc.rect(170, totalY, 22, 10);
                doc.text(String(grandTotal).replace('.', ','), 190, totalY + 6.5, {
                    align: 'right'
                });

                let footerY = pageHeight - 24;

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(8);
                doc.text(
                    "DOCUMENT D'ACHAT - SYSTÈME ERP",
                    pageWidth / 2,
                    footerY, {
                        align: 'center'
                    }
                );
                doc.text(
                    "MERCI DE VÉRIFIER LES QUANTITÉS ET LES PRIX À LA RÉCEPTION",
                    pageWidth / 2,
                    footerY + 4, {
                        align: 'center'
                    }
                );

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(8);
                doc.text(
                    "Siège Social : 14 Magasin 1 Lot Taisir Quartier Sidi Ghanem - Marrakech",
                    pageWidth / 2,
                    footerY + 10, {
                        align: 'center'
                    }
                );
                doc.text(
                    "Tél. : 0524 33 65 14 / 06 61 28 44 87 - E-mail : italopieces2015@gmail.com",
                    pageWidth / 2,
                    footerY + 14, {
                        align: 'center'
                    }
                );

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
