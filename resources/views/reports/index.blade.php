@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">
        <h2 class="mb-4">📊 Rapport Mensuel</h2>

        <!-- FILTER -->
        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="month" name="month" class="form-control" value="{{ $month }}">
                </div>
        
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filtrer</button>
                </div>
        
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger w-100" id="downloadReportPdf">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </button>
                </div>
            </div>
        </form>
        <!-- CARDS -->
        <div class="row g-3">

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Total Ventes</h6>
                    <h4 class="text-primary">{{ number_format($totalSales, 2) }} MAD</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Total Payé</h6>
                    <h4 class="text-success">{{ number_format($totalPaid, 2) }} MAD</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Reste à recevoir</h6>
                    <h4 class="text-danger">{{ number_format($totalRemaining, 2) }} MAD</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Total Achats</h6>
                    <h4 class="text-warning">{{ number_format($totalPurchases, 2) }} MAD</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Dépenses</h6>
                    <h4 class="text-secondary">{{ number_format($totalExpenses, 2) }} MAD</h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h6>Profit Net</h6>
                    <h4 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($netProfit, 2) }} MAD
                    </h4>
                </div>
            </div>

        </div>

        <!-- CHART -->
        <div class="mt-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1 fw-bold">Analyse Financière</h4>
                            <p class="text-muted mb-0">Vue claire des ventes, achats, dépenses et profit</p>
                        </div>
                    </div>

                    <div style="height: 380px;">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="reportPdfData"
    data-month="{{ $month }}"
    data-sales="{{ number_format($totalSales, 2, '.', '') }}"
    data-paid="{{ number_format($totalPaid, 2, '.', '') }}"
    data-remaining="{{ number_format($totalRemaining, 2, '.', '') }}"
    data-purchases="{{ number_format($totalPurchases, 2, '.', '') }}"
    data-expenses="{{ number_format($totalExpenses, 2, '.', '') }}"
    data-profit="{{ number_format($netProfit, 2, '.', '') }}">
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportCanvas = document.getElementById('reportChart');

            if (!reportCanvas) return;

            const reportData = [
                {{ $totalSales ?? 0 }},
                {{ $totalPurchases ?? 0 }},
                {{ $totalExpenses ?? 0 }},
                {{ $netProfit ?? 0 }}
            ];

            new Chart(reportCanvas, {
                type: 'bar',
                data: {
                    labels: ['Ventes', 'Achats', 'Dépenses', 'Profit Net'],
                    datasets: [{
                        label: 'Montant (MAD)',
                        data: reportData,
                        borderRadius: {
                            topLeft: 10,
                            topRight: 10,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        borderSkipped: false,
                        maxBarThickness: 55,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(107, 114, 128, 0.85)',
                            {!! $netProfit >= 0 ? "'rgba(16, 185, 129, 0.85)'" : "'rgba(239, 68, 68, 0.85)'" !!}
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(107, 114, 128, 1)',
                            {!! $netProfit >= 0 ? "'rgba(16, 185, 129, 1)'" : "'rgba(239, 68, 68, 1)'" !!}
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            right: 15,
                            bottom: 0,
                            left: 10
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#111827',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw ?? 0;
                                    return ' ' + Number(value).toLocaleString('fr-FR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' MAD';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#374151',
                                font: {
                                    size: 13,
                                    weight: '600'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6B7280',
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return Number(value).toLocaleString('fr-FR') + ' MAD';
                                }
                            }
                        }
                    }
                }
            });
        });

        ////for Dwld pdf 

document.addEventListener('DOMContentLoaded', function () {
    const pdfBtn = document.getElementById('downloadReportPdf');
    const reportData = document.getElementById('reportPdfData');

    if (!pdfBtn || !reportData) return;

    pdfBtn.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');

        const month = reportData.dataset.month || '';
        const totalSales = reportData.dataset.sales || '0.00';
        const totalPaid = reportData.dataset.paid || '0.00';
        const totalRemaining = reportData.dataset.remaining || '0.00';
        const totalPurchases = reportData.dataset.purchases || '0.00';
        const totalExpenses = reportData.dataset.expenses || '0.00';
        const netProfit = reportData.dataset.profit || '0.00';

        const pageWidth = doc.internal.pageSize.getWidth();

        // Header
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(18);
        doc.text('Rapport Mensuel', pageWidth / 2, 20, { align: 'center' });

        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.text(`Période: ${month}`, pageWidth / 2, 28, { align: 'center' });

        // Line
        doc.setDrawColor(180, 180, 180);
        doc.line(15, 35, 195, 35);

        // Section title
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(13);
        doc.text('Résumé Financier', 15, 48);

        // Boxes
        const items = [
            ['Total Ventes', totalSales + ' MAD'],
            ['Total Payé', totalPaid + ' MAD'],
            ['Reste à recevoir', totalRemaining + ' MAD'],
            ['Total Achats', totalPurchases + ' MAD'],
            ['Dépenses', totalExpenses + ' MAD'],
            ['Profit Net', netProfit + ' MAD'],
        ];

        let startY = 58;
        const leftX = 15;
        const boxW = 85;
        const boxH = 20;
        const gapX = 10;
        const gapY = 8;

        items.forEach((item, index) => {
            const col = index % 2;
            const row = Math.floor(index / 2);

            const x = leftX + col * (boxW + gapX);
            const y = startY + row * (boxH + gapY);

            doc.setDrawColor(220, 220, 220);
            doc.roundedRect(x, y, boxW, boxH, 3, 3);

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text(item[0], x + 5, y + 7);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(11);
            doc.text(item[1], x + 5, y + 15);
        });

        // Profit note
        const noteY = startY + 3 * (boxH + gapY) + 8;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(12);
        doc.text('Observation', 15, noteY);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);

        const profitValue = parseFloat(netProfit);
        let observation = 'Le résultat du mois est stable.';
        if (profitValue > 0) {
            observation = 'Le mois est bénéficiaire. Les ventes couvrent les achats et les dépenses.';
        } else if (profitValue < 0) {
            observation = 'Le mois est déficitaire. Vérifiez les achats et les dépenses.';
        }

        const splitText = doc.splitTextToSize(observation, 175);
        doc.text(splitText, 15, noteY + 8);

        // Footer
        doc.setFontSize(9);
        doc.setTextColor(100);
        doc.text('Rapport généré depuis le système ERP', pageWidth / 2, 285, { align: 'center' });

        doc.save(`rapport_${month}.pdf`);
    });
});
    </script>
@endsection
