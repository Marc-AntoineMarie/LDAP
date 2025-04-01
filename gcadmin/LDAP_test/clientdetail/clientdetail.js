document.addEventListener("DOMContentLoaded", function() {
    // Fonction pour ajouter un client
    document.getElementById('add-client').addEventListener('click', function() {
        const firstName = prompt("Entrez le prénom du client :");
        const lastName = prompt("Entrez le nom du client :");
        const email = prompt("Entrez l'email du client :");
        const extension = prompt("Entrez l'extension du client :");
        const code = prompt("Entrez le code du client :");

        if (firstName && lastName && email && extension && code) {
            // Créer une nouvelle ligne dans le tableau
            const newRow = document.createElement('tr');

            // Créer les cellules
            newRow.innerHTML = `
                <td><input type="checkbox" class="client-checkbox"></td> <!-- Case à cocher pour ce client -->
                <td>${firstName}</td>
                <td>${lastName}</td>
                <td>${email}</td>
                <td>${extension}</td>
                <td>${code}</td>
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

//ajout d'un contact

document.getElementById('add-client').addEventListener('click', function () {
    const firstName = prompt("Entrez le prénom du client :");
    const lastName = prompt("Entrez le nom du client :");
    const email = prompt("Entrez l'email du client :");
    const extension = prompt("Entrez l'extension du client :");
    const code = prompt("Entrez le code du client :");

    if (firstName && lastName && email && extension && code) {
        fetch('annuaire.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_contact',
                firstname: firstName,
                lastname: lastName,
                email: email,
                extension: extension,
                code: code,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert('Contact ajouté avec succès');
                    location.reload(); // Recharge la page pour afficher le contact ajouté
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch((error) => console.error('Erreur :', error));
    }
});
