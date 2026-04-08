<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Facturation - Système d'Inventaire</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
        }

        .invoice-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 2px;
        }

        label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #e9f1ff;
        }

        .total-section p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
        }

        .total-section strong {
            font-size: 1.3rem;
            color: #0d6efd;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #084298;
            border-color: #084298;
        }

        .btn-danger:hover {
            background-color: #a71d2a;
            border-color: #a71d2a;
        }

        .delete-item i {
            font-size: 1.1rem;
        }

        .search-input {
            min-width: 150px;
        }

        .readonly-input {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <h1>Créer une Facture</h1>
        <!-- Datalist Produits -->
        <datalist id="products-list">
            @foreach ($products as $product)
                <option value="{{ $product['Referonce'] }}" data-referonce="{{ $product['Referonce'] }}"
                    data-designation="{{ $product['Designation'] }}" data-price="{{ $product['prace_sell'] }}"
                    data-stock="{{ $product['Quantite'] }}">
                    {{ $product['Designation'] }}
                    @if ($product['Quantite'] == 0)
                        (Rupture)
                    @endif
                </option>
            @endforeach
        </datalist> <!-- Datalist Clients -->
        <datalist id="customers-list">
            @foreach ($customers as $customer)
                <option value="{{ $customer->name }}" data-id="{{ $customer->id }}"
                    data-address="{{ $customer->address ?? '' }}">
                </option>
            @endforeach
        </datalist>

        <form method="POST" action="{{ route('facture.store') }}" id="invoiceForm">
            @csrf

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="customer_search">Client</label>
                    <input type="text" id="customer_search" name="customer_search" class="form-control"
                        list="customers-list" placeholder="Rechercher un client..." required>
                    <input type="hidden" name="customer_id" id="customer_id">
                </div>

                <div class="col-md-3">
                    <label for="invoice_date">Date Facture</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}"
                        id="invoice_date" class="form-control" required>
                </div>



                <div class="col-md-3">
                    <label for="due_date">Date d'échéance</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="invoice_number">Numéro Facture</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                    value="{{ old('invoice_number') }}" placeholder="Laisser vide pour génération automatique">
            </div>

            <h3 class="mb-3">Articles</h3>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th style="width: 18%;">Référence</th>
                            <th style="width: 30%;">Désignation</th>
                            <th style="width: 15%;">Prix Unitaire (MAD)</th>
                            <th style="width: 10%;">Quantité</th>
                            <th style="width: 15%;">Total (MAD)</th>
                            <th style="width: 12%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="items-list">
                        <tr>
                            <td>
                                <input type="text" class="form-control product-search search-input"
                                    list="products-list" placeholder="Référence..." required>
                                <input type="hidden" name="items[0][referonce]" class="product-hidden">
                            </td>
                            <td>
                                <input type="text" name="items[0][designation]"
                                    class="form-control designation readonly-input" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[0][price]" class="form-control price" step="0.01"
                                    min="0" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity"
                                    min="1" value="1" required>
                            </td>
                            <td>
                                <span class="total">0.00</span> MAD
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm delete-item" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-primary mb-4" id="add-item">
                <i class="fas fa-plus"></i> Ajouter Article
            </button>

            <div class="total-section text-end">
                <p>Sous-total : <span id="subtotal">0.00</span> MAD</p>
                <p>Taxe (20%) : <span id="tax">0.00</span> MAD</p>
                <p><strong>Total Général : <span id="grand-total">0.00</span> MAD</strong></p>
            </div>

            <!-- 🔥 status -->

            <div class="col-md-4">
                <label>Montant payé</label>
                <input type="number" name="paid_amount" class="form-control" step="0.01" min="0"
                    value="0">
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-lg ">
                    Enregistrer la Facture
                </button>
                <button type="button" class="btn btn-danger me-2" id="save-pdf">
                    <i class="btn fas fa-file-pdf"></i> Enregistrer en PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const itemsList = document.getElementById('items-list');
            let itemIndex = 1;

            // =========================
            // CALCUL TOTAL
            // =========================
            function calculateTotals() {
                let subtotal = 0;

                itemsList.querySelectorAll('tr').forEach(row => {
                    const price = parseFloat(row.querySelector('.price')?.value) || 0;
                    const quantity = parseInt(row.querySelector('.quantity')?.value) || 0;
                    const total = price * quantity;

                    row.querySelector('.total').textContent = total.toFixed(2);
                    subtotal += total;
                });

                const tax = subtotal * 0;
                const grandTotal = subtotal + tax;

                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('tax').textContent = tax.toFixed(2);
                document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
            }

            // =========================
            // GET PRODUCT
            // =========================
            function getProduct(referonce) {
                const options = document.querySelectorAll('#products-list option');

                for (let opt of options) {
                    if (opt.value === referonce) {
                        return {
                            referonce: opt.dataset.referonce,
                            designation: opt.dataset.designation,
                            price: opt.dataset.price,
                            stock: opt.dataset.stock
                        };
                    }
                }

                return null;
            }

            // =========================
            // GET CLIENT
            // =========================
            function getClient(name) {
                const options = document.querySelectorAll('#customers-list option');

                for (let opt of options) {
                    if (opt.value === name) {
                        return {
                            id: opt.dataset.id,
                            address: opt.dataset.address || ''
                        };
                    }
                }

                return null;
            }

            // =========================
            // CLIENT SEARCH
            // =========================
            const customerSearch = document.getElementById('customer_search');
            const customerId = document.getElementById('customer_id');
            const customerAddress = document.getElementById('customer_address');

            customerSearch.addEventListener('input', function() {
                const client = getClient(this.value);

                if (client) {
                    customerId.value = client.id;
                    customerAddress.value = client.address;
                } else {
                    customerId.value = '';
                    customerAddress.value = '';
                }
            });

            // =========================
            // PRODUCT SELECT + STOCK CHECK
            // =========================
            itemsList.addEventListener('input', function(e) {

                if (e.target.classList.contains('product-search')) {

                    const row = e.target.closest('tr');
                    const product = getProduct(e.target.value);

                    if (product) {

                        const stock = parseInt(product.stock) || 0;

                        // ❌ rupture
                        if (stock === 0) {
                            alert('⚠️ هذا المنتج غير متوفر في المخزون');

                            row.querySelector('.product-hidden').value = '';
                            row.querySelector('.designation').value = '';
                            row.querySelector('.price').value = '';
                            row.querySelector('.total').textContent = '0.00';

                            return;
                        }

                        // fill data
                        row.querySelector('.product-hidden').value = product.referonce;
                        row.querySelector('.designation').value = product.designation;
                        row.querySelector('.price').value = parseFloat(product.price).toFixed(2);

                        const quantityInput = row.querySelector('.quantity');

                        // 🔥 check quantity > stock
                        quantityInput.oninput = function() {
                            let qty = parseInt(this.value) || 0;

                            if (qty > stock) {
                                alert('⚠️ الكمية المطلوبة أكبر من المخزون (Stock: ' + stock + ')');
                                this.value = stock;
                            }

                            calculateTotals();
                        };

                    } else {
                        // reset
                        row.querySelector('.product-hidden').value = '';
                        row.querySelector('.designation').value = '';
                        row.querySelector('.price').value = '';
                        row.querySelector('.total').textContent = '0.00';
                    }

                    calculateTotals();
                }

                // update totals
                if (
                    e.target.classList.contains('price') ||
                    e.target.classList.contains('quantity')
                ) {
                    calculateTotals();
                }
            });

            // =========================
            // ADD ROW
            // =========================
            document.getElementById('add-item').addEventListener('click', function() {

                const newRow = document.createElement('tr');

                newRow.innerHTML = `
        <td>
            <input type="text" class="form-control product-search search-input"
                list="products-list" placeholder="Référence..." required>
            <input type="hidden" name="items[${itemIndex}][referonce]" class="product-hidden">
        </td>
        <td>
            <input type="text" name="items[${itemIndex}][designation]"
                class="form-control designation readonly-input" readonly>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][price]"
                class="form-control price" step="0.01" min="0" required>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][quantity]"
                class="form-control quantity" min="1" value="1" required>
        </td>
        <td>
            <span class="total">0.00</span> MAD
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm delete-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

                itemsList.appendChild(newRow);
                itemIndex++;
            });

            // =========================
            // DELETE ROW
            // =========================
            itemsList.addEventListener('click', function(e) {

                if (e.target.closest('.delete-item')) {

                    const rows = itemsList.querySelectorAll('tr');

                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                    } else {
                        const row = e.target.closest('tr');
                        row.querySelector('.product-search').value = '';
                        row.querySelector('.product-hidden').value = '';
                        row.querySelector('.designation').value = '';
                        row.querySelector('.price').value = '';
                        row.querySelector('.quantity').value = 1;
                        row.querySelector('.total').textContent = '0.00';
                    }

                    calculateTotals();
                }
            });

            calculateTotals();
        });
        document.getElementById('save-pdf').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();

            let invoiceNumber = document.getElementById('invoice_number').value.trim();

            if (!invoiceNumber) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                invoiceNumber = `INV-${year}${month}${day}-${hours}${minutes}${seconds}`;

                // مهم: نكتب نفس الرقم فـ input باش يتسيفط مع الفورم
                document.getElementById('invoice_number').value = invoiceNumber;
            }
            const rawDate = document.getElementById('invoice_date').value;

            const invoiceDate = rawDate ?
                new Date(rawDate).toLocaleDateString('fr-FR') :
                '';

            const customerValue = document.getElementById('customer_search').value || 'Client';

            function getClientData(name) {
                const options = document.querySelectorAll('#customers-list option');

                for (let opt of options) {
                    if (opt.value === name) {
                        return {
                            name: opt.value,
                            address: opt.dataset.address || ''
                        };
                    }
                }

                return {
                    name: name || 'Client',
                    address: ''
                };
            }

            const clientData = getClientData(customerValue);
            const customerName = clientData.name;
            const customerAddress = clientData.address;
            const grandTotal = document.getElementById('grand-total').textContent || '0.00';

            const logoUrl = @json(!empty($company?->logo) ? asset('storage/' . $company->logo) : asset('images/img.png'));
            const companyCity = @json($company->city ?? 'Marrakech');
            const companyName = @json($company->company_name ?? '');
            const companyAddress = @json($company->address ?? '');
            const companyPhone = @json($company->phone ?? '');
            const companyEmail = @json($company->email ?? '');
            const footerNote = @json(
                $company->footer_note ??
                    "LES TURBOCHARGEURS, LES PIÈCES ÉLECTRONIQUES ET HYDRAULIQUES NE SONT PAS COUVERTS PAR LA GARANTIE AUCUN RETOUR OU AVOIR N'EST ACCEPTÉ");
            const footerContact = @json($company->footer_contact ?? '');

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = logoUrl;

            img.onload = function() {
                // =========================
                // LOGO
                // =========================
                const logoWidth = pageWidth;
                const logoHeight = 35; // بدلها 30 أو 40 حسب الصورة
                const logoX = 0;
                const logoY = 0;

                doc.addImage(img, 'PNG', logoX, logoY, logoWidth, logoHeight);
                // =========================
                // NOM SOCIETE
                // =========================
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(16);
                doc.setTextColor(70, 85, 110);
                doc.text(companyName.toUpperCase(), pageWidth / 2, 42, {
                    align: 'center'
                });

                doc.setDrawColor(0, 102, 204);
                doc.setLineWidth(0.4);
                doc.line(65, 46, pageWidth - 65, 46);

                // =========================
                // HEADER INFOS
                // =========================
                doc.setTextColor(0, 0, 0);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                // ✅ numéro facture بحال الأول
                doc.text(`BON DE LIVRAISON N° ${invoiceNumber}`, 12, 56);

                // ✅ date فوق box client وعلى اليمين
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(10);
                doc.text(`${companyCity}, le : ${invoiceDate}`, pageWidth - 19, 56, {
                    align: 'right'
                });


                // =========================
                // CLIENT BOX
                // =========================
                doc.setDrawColor(170, 170, 170);
                doc.setLineWidth(0.25);
                doc.roundedRect(120, 58, 72, 24, 3, 3);

                // 🔹 CLIENT LABEL
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(9);
                doc.setTextColor(90, 100, 125);

                const labelX = 126;
                const labelY = 64;

                doc.text('CLIENT :', labelX, labelY);

                // 🔹 نحسب طول "CLIENT :"
                const labelWidth = doc.getTextWidth('CLIENT :');

                // 🔹 POSITION ديال الاسم
                const nameX = labelX + labelWidth + 3;

                // 🔹 CLIENT NAME
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(11);
                doc.setTextColor(0, 0, 0);

                doc.text(customerName.toUpperCase(), nameX, 64, {
                    maxWidth: 50
                });

                // 🔹 CLIENT ADDRESS
                if (customerAddress) {
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(8);

                    const addressLines = doc.splitTextToSize(customerAddress, 50);

                    doc.text(addressLines, nameX, 72);
                }


                // =========================
                // TABLE DATA
                // =========================
                const rows = [];
                document.querySelectorAll('#items-list tr').forEach(row => {
                    const referonce = row.querySelector('.product-hidden')?.value || '';
                    const designation = row.querySelector('.designation')?.value || '';
                    const quantity = row.querySelector('.quantity')?.value || '1';
                    const price = parseFloat(row.querySelector('.price')?.value || 0).toFixed(2);
                    const total = row.querySelector('.total')?.textContent || '0.00';

                    if (referonce || designation) {
                        rows.push([
                            referonce,
                            designation,
                            quantity.replace('.', ','),
                            price.replace('.', ','),
                            String(total).replace('.', ',')
                        ]);
                    }
                });

                const minRows = 15;
                while (rows.length < minRows) {
                    rows.push(['', '', '', '', '']);
                }

                // =========================
                // TABLE CLEAN
                // =========================
                doc.autoTable({
                    startY: 84,
                    head: [
                        [
                            'Référence',
                            'Désignation',
                            'Quantité',
                            'P.U Net',
                            'Montant Net'
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

                        // outer border
                        doc.setDrawColor(170, 170, 170);
                        doc.setLineWidth(0.2);
                        doc.rect(x, y, tableWidth, totalHeight);

                        // vertical lines only
                        const col1 = x + 28;
                        const col2 = x + 108;
                        const col3 = x + 130;
                        const col4 = x + 154;

                        doc.line(col1, y, col1, y + totalHeight);
                        doc.line(col2, y, col2, y + totalHeight);
                        doc.line(col3, y, col3, y + totalHeight);
                        doc.line(col4, y, col4, y + totalHeight);

                        // header separator only
                        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
                    }
                });

                // =========================
                // TOTAL BOX
                // =========================
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
                doc.setLineWidth(0.2);
                doc.line(12, footerY - 6, pageWidth - 12, footerY - 6);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(7.5);
                doc.setTextColor(90, 90, 90);
                doc.text(
                    footerNote,
                    pageWidth / 2,
                    footerY - 1, {
                        align: 'center',
                        maxWidth: 180
                    }
                );

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7.5);

                // ✅ adresse société لتحت
                if (companyAddress) {
                    doc.text(
                        companyAddress,
                        pageWidth / 2,
                        footerY + 7, {
                            align: 'center',
                            maxWidth: 180
                        }
                    );
                }

                doc.text(
                    footerContact ||
                    `${companyPhone ? 'Tél. : ' + companyPhone : ''}${companyEmail ? ' - E-mail : ' + companyEmail : ''}`,
                    pageWidth / 2,
                    footerY + 12, {
                        align: 'center',
                        maxWidth: 180
                    }
                );

                doc.save(`facture_${invoiceNumber}.pdf`);
                document.getElementById('invoiceForm').submit();
            };

            img.onerror = function() {
                alert("Erreur lors du chargement du logo.");
            };
        });
    </script>
</body>

</html>
