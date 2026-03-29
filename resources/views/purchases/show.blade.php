@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="shadow p-4 bg-white rounded">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                Détails Achat - {{ $purchase->purchase_code }}
            </h2>
        
            <div class="d-flex gap-2">
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
        
                <button type="button" class="btn btn-danger" id="save-pdf">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </button>
            </div>
        </div>
        <!-- INFO -->
        <div class="row mb-4">

            <div class="col-md-4">
                <strong>Fournisseur:</strong><br>
                {{ $purchase->supplier->name ?? '-' }}
            </div>

            <div class="col-md-4">
                <strong>Date:</strong><br>
                {{ $purchase->purchase_date }}
            </div>

            <div class="col-md-4">
                <strong>Status:</strong><br>

                @if($purchase->status == 'reçu')
                    <span class="badge bg-success">Reçu</span>
                @else
                    <span class="badge bg-warning text-dark">En attente</span>
                @endif
            </div>

        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">

                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Referonce</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Achat</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody id="purchase-items-list">
                    @foreach($purchase->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="product-Referonce">{{ $item->product->Referonce ?? '-' }}</td>

                            <td class="product-designation">{{ $item->product->Designation ?? '-' }}</td>
                            <td class="product-quantity">{{ $item->quantity }}</td>
                            <td class="product-price">{{ number_format($item->buy_price, 2, '.', '') }}</td>
                            <td class="product-total">{{ number_format($item->quantity * $item->buy_price, 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- TOTAL -->
        <div class="text-end mt-4">
            <h4 class="text-success">
                Total Général: {{ number_format($purchase->total, 2) }} MAD
            </h4>
        </div>

    </div>

</div>


@php
    $supplierName = optional($purchase->supplier)->name ?: 'Fournisseur';
    $grandTotal = number_format($purchase->total, 2, '.', '');
    $formattedDate = \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y');

@endphp


<script>
  document.getElementById('save-pdf').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    const purchaseCode = @json($purchase->purchase_code);
    const purchaseDate = @json($formattedDate);
        const supplierName = @json($supplierName);
    const grandTotal = @json($grandTotal);

    const logoUrl = '{{ asset("images/img.png") }}';
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = logoUrl;

    img.onload = function () {
   
            // =========================
            // LOGO CENTER TOP
            // =========================
            const logoWidth = 85;
            const logoHeight = 28;
            const logoX = (pageWidth - logoWidth) / 2;
            const logoY = 8;
    
            doc.addImage(img, 'PNG', logoX, logoY, logoWidth, logoHeight);
    
            doc.setDrawColor(120, 120, 120);
            doc.setLineWidth(0.3);
            doc.line(12, 40, pageWidth - 12, 40);
    
            // =========================
            // HEADER INFOS
            // =========================
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text(`BON D'ACHAT N° ${purchaseCode}`, 12, 48);
            doc.text(`FOURNISSEUR`, 12, 55);
    
            doc.text(`Marrakech, le : ${purchaseDate}`, pageWidth - 17, 44, {
                align: 'right'
            });
    
            // Box fournisseur
            doc.rect(122, 46, 70, 20);
            doc.text(String(supplierName).toUpperCase(), 157, 60, {
                align: 'center'
            });
    
            // =========================
            // TABLE DATA
            // =========================
            const rows = [];
document.querySelectorAll('#purchase-items-list tr').forEach(row => {
    const designation = row.querySelector('.product-designation')?.textContent?.trim() || '';
    const Referonce = row.querySelector('.product-Referonce')?.textContent?.trim() || '';
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

// tabel ibqa fiix wakha kayn ghi produit wahed
const minRows = 15;
while (rows.length < minRows) {
    rows.push(['', '', '', '', '']);
}

doc.autoTable({
    startY: 70,
    head: [[
        'Référence',
        'Désignation',
        'Quantité',
        'P.U Achat',
        'Montant'
    ]],
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
        0: { cellWidth: 30, halign: 'left' },
        1: { cellWidth: 62, halign: 'left' },
        2: { cellWidth: 22, halign: 'center' },
        3: { cellWidth: 28, halign: 'center' },
        4: { cellWidth: 38, halign: 'center' }
    },
    margin: { left: 12, right: 12 },
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

        // line dyal header فقط
        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
    }
});
    
            // =========================
            // TOTAL BOX
            // =========================
            const totalY = 200;
    
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
    
            doc.setFillColor(0, 102, 204);
            doc.rect(142, totalY, 28, 10, 'F');
            doc.setTextColor(255, 255, 255);
            doc.text('TOTAL', 156, totalY + 6.5, { align: 'center' });
    
            doc.setFillColor(255, 255, 255);
            doc.setTextColor(0, 0, 0);
            doc.rect(170, totalY, 22, 10);
            doc.text(String(grandTotal).replace('.', ','), 190, totalY + 6.5, {
                align: 'right'
            });
    
            // =========================
            // FOOTER
            // =========================
            let footerY = pageHeight - 24;
    
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text(
                "DOCUMENT D'ACHAT - SYSTÈME ERP",
                pageWidth / 2,
                footerY,
                { align: 'center' }
            );
            doc.text(
                "MERCI DE VÉRIFIER LES QUANTITÉS ET LES PRIX À LA RÉCEPTION",
                pageWidth / 2,
                footerY + 4,
                { align: 'center' }
            );
    
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.text(
                "Siège Social : 14 Magasin 1 Lot Taisir Quartier Sidi Ghanem - Marrakech",
                pageWidth / 2,
                footerY + 10,
                { align: 'center' }
            );
            doc.text(
                "Tél. : 0524 33 65 14 / 06 61 28 44 87 - E-mail : italopieces2015@gmail.com",
                pageWidth / 2,
                footerY + 14,
                { align: 'center' }
            );
    
            doc.save(`achat_${purchaseCode}.pdf`);
        };
    
        img.onerror = function () {
            alert("Erreur lors du chargement du logo.");
        };
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    
@endsection




