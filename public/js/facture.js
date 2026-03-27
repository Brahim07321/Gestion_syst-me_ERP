document.addEventListener('DOMContentLoaded', () => {
    const comboInputs = document.querySelectorAll('.combo-input');
    const comboContainers = document.querySelectorAll('.combo-container');

    comboContainers.forEach((container) => {
        const comboInput = container.querySelector('.combo-input');
        const comboOptions = container.querySelector('.combo-options');
        const options = container.querySelectorAll('.combo-option');

        // Show dropdown
        comboInput.addEventListener('click', () => {
            comboOptions.style.display = 'block';
        });

        // Filter options as the user types
        comboInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Select an option and auto-fill fields
        options.forEach(option => {
            option.addEventListener('click', () => {
                const row = container.closest('tr');
                comboInput.value = option.dataset.referonce;
                row.querySelector('.designation').value = option.dataset.designation;
                row.querySelector('.price').value = option.dataset.price;

                // Update total
                const quantity = parseFloat(row.querySelector('.quantity').value || 1);
                const price = parseFloat(option.dataset.price || 0);
                row.querySelector('.total').textContent = `${(quantity * price).toFixed(2)} MAD`;

                comboOptions.style.display = 'none';
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.combo-container')) {
                comboOptions.style.display = 'none';
            }
        });
    });

    // Calculate total on quantity change
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('quantity')) {
            const row = e.target.closest('tr');
            const quantity = parseFloat(e.target.value || 1);
            const price = parseFloat(row.querySelector('.price').value || 0);
            row.querySelector('.total').textContent = `${(quantity * price).toFixed(2)} MAD`;
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
    // Initialisation des dates de facture
    const formatDate = (date) => date.toLocaleDateString('fr-FR');
    document.getElementById('invoice-date').textContent = formatDate(new Date());
    document.getElementById('due-date').textContent = formatDate(new Date(Date.now() + 7 * 86400000));

    const itemsList = document.getElementById('items-list');

    // Ajouter un nouvel article
    document.getElementById('add-item').addEventListener('click', () => {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <div class="combo-container">
                    <input type="text" class="combo-input" placeholder="Sélectionnez ou tapez..." id="comboInput">
                    <div class="combo-options">
                        <!-- Les options doivent être générées dynamiquement -->
                        ${generateComboOptions()}
                    </div>
                </div>
            </td>
            <td><input type="text" class="form-control designation" placeholder="Nom produit" readonly></td>
            <td><input type="number" class="form-control price" step="0.01"></td>
            <td><input type="number" class="form-control quantity" min="1" value="1"></td>
            <td><span class="total">0.00</span> MAD</td>
            <td class="no-print"><i class="fas fa-times delete-item"></i></td>
        `;
        itemsList.appendChild(newRow);
        initializeComboBox(newRow);
    });

    // Supprimer un article
    itemsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-item')) {
            e.target.closest('tr').remove();
            calculateTotal();
        }
    });

    // Calcul automatique lorsque les prix ou les quantités changent
    itemsList.addEventListener('input', (e) => {
        if (e.target.classList.contains('price') || e.target.classList.contains('quantity')) {
            const row = e.target.closest('tr');
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const quantity = parseInt(row.querySelector('.quantity').value) || 1;
            row.querySelector('.total').textContent = (price * quantity).toFixed(2);
            calculateTotal();
        }
    });

    // Réinitialiser la facture
    document.getElementById('reset-invoice').addEventListener('click', () => {
        itemsList.innerHTML = `<tr>${document.querySelector('#items-list tr').innerHTML}</tr>`;
        calculateTotal();
    });

    // Fonction pour initialiser les combo boxes
    function initializeComboBox(row) {
        const comboInput = row.querySelector('.combo-input');
        const comboOptions = row.querySelector('.combo-options');
        const designationInput = row.querySelector('.designation');
        const priceInput = row.querySelector('.price');

        comboInput.addEventListener('focus', () => {
            comboOptions.style.display = 'block';
        });

        comboInput.addEventListener('blur', () => {
            setTimeout(() => {
                comboOptions.style.display = 'none';
            }, 200);
        });

        comboOptions.addEventListener('click', (e) => {
            if (e.target.classList.contains('combo-option')) {
                const selectedOption = e.target;
                comboInput.value = selectedOption.dataset.referonce;
                designationInput.value = selectedOption.dataset.designation;
                priceInput.value = parseFloat(selectedOption.dataset.price).toFixed(2);

                // Recalculer le total pour cette ligne et le total général
                const quantity = parseInt(row.querySelector('.quantity').value) || 1;
                row.querySelector('.total').textContent = (priceInput.value * quantity).toFixed(2);
                calculateTotal();
            }
        });
    }

    // Fonction pour calculer le total de la facture
    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.total').forEach(total => {
            subtotal += parseFloat(total.textContent) || 0;
        });

        const tax = subtotal * 0.2; // Taxe de 20%
        const grandTotal = subtotal + tax;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('tax').textContent = tax.toFixed(2);
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }

    // Fonction pour générer les options dynamiques pour le combo box
    function generateComboOptions() {
        return `
            @foreach ($products as $product)
                <div class="combo-option" 
                     data-referonce="{{ $product->Referonce }}" 
                     data-designation="{{ $product->Designation }}" 
                     data-price="{{ $product->prace_sell }}">
                    {{ $product->Referonce }}
                </div>
            @endforeach
        `;
    }

    // Initialiser les combo boxes sur les lignes existantes
    document.querySelectorAll('#items-list tr').forEach(row => initializeComboBox(row));

    // Calcul initial
    calculateTotal();
});


document.addEventListener('DOMContentLoaded', () => {
    const comboInput = document.getElementById('comboInput');
    const comboOptions = document.getElementById('comboOptions');
    const options = document.querySelectorAll('.combo-option');

    // Click event to handle selection
    comboOptions.addEventListener('click', (event) => {
        if (event.target.classList.contains('combo-option')) {
            const selectedValue = event.target.dataset.value; // Get the data-value attribute
            console.log(selectedValue); // Debugging the output
            if (selectedValue) {
                comboInput.value = event.target.textContent; // Set input value to selected option's text
                comboOptions.style.display = 'none'; // Hide options
            } else {
                console.warn('Selected option has no data-value attribute.');
            }
        }
    });

    // Open the dropdown
    comboInput.addEventListener('click', () => {
        comboOptions.style.display = 'block';
    });

    // Filter options based on input
    comboInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.combo-container')) {
            comboOptions.style.display = 'none';
        }
    });
});
