document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.clientId;
            if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        id: clientId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Erreur lors de la suppression du client');
                    }
                })
                .catch(error => {
                    console.error('Erreur :', error);
                    alert('Une erreur s\'est produite.');
                });
            }
        });
    });
});