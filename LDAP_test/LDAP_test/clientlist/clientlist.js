document.addEventListener("DOMContentLoaded", function() {
    // Fonction pour ajouter un client
    document.getElementById('add-client').addEventListener('click', function() {
        const clientName = prompt("Entrez le nom du client :");
        const phone = prompt("Entrez le téléphone du client :");
        const postalCode = prompt("Entrez le code postal du client :");
        const contact = prompt("Entrez le contact du client :");

        if (clientName && phone && postalCode && contact) {
            // Créer une nouvelle ligne dans le tableau
            const newRow = document.createElement('tr');

            // Créer les cellules avec le logo-placeholder par défaut
            newRow.innerHTML = `
                <td><input type="checkbox" class="client-checkbox"></td>
                <td class="logo-cell">
                    <div class="logo-placeholder"></div>
                </td>
                <td class="card-content"><h2>${clientName}</h2></td>
                <td>${phone}</td>
                <td>${postalCode}</td>
                <td>${contact}</td>
                <td><button class="btn-delete">✖</button></td>
            `;

            // Ajouter la nouvelle ligne à la liste de clients
            document.getElementById('client-list').appendChild(newRow);

            // Ajouter un événement pour le bouton de suppression
            const deleteButton = newRow.querySelector('.btn-delete');
            deleteButton.addEventListener('click', function() {
                newRow.remove(); // Supprimer la ligne
            });

            // Écouteur d'événement pour changer le style de la ligne lorsque la case est cochée
            const checkbox = newRow.querySelector('.client-checkbox');
            checkbox.addEventListener('change', function() {
                if (checkbox.checked) {
                    newRow.style.backgroundColor = '#e9f5ff'; // Couleur de fond lorsqu'elle est cochée
                } else {
                    newRow.style.backgroundColor = ''; // Rétablir la couleur par défaut
                }
            });
        }
    });

    // Événement de suppression pour les clients existants
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const row = button.closest('tr');
            row.remove(); // Supprimer la ligne
        });
    });

    // Gérer la sélection de tous les clients
    const selectAllCheckbox = document.getElementById('select-all');
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.client-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked; // Coche ou décoche toutes les cases
            // Change le style de la ligne en fonction de l'état de la case
            const row = checkbox.closest('tr');
            if (checkbox.checked) {
                row.style.backgroundColor = '#e9f5ff'; // Couleur de fond lorsqu'elle est cochée
            } else {
                row.style.backgroundColor = ''; // Rétablir la couleur par défaut
            }
        });
    });

    // Ajouter l'événement pour les cases à cocher existantes
    document.querySelectorAll('.client-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = checkbox.closest('tr');
            if (checkbox.checked) {
                row.style.backgroundColor = '#e9f5ff'; // Couleur de fond lorsqu'elle est cochée
            } else {
                row.style.backgroundColor = ''; // Rétablir la couleur par défaut
            }
        });
    });
});

