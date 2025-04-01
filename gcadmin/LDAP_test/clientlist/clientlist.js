function confirmDelete(clientId) {
    if(confirm("Etes-vous sur de vouloir supprimer ce client ?")) {
        //Requete AJAX pour supprimer l'user
        deleteClient(clientId);
    }
}

// function deleteClient(clientId) {
//     const xml = new XMLHttpRequest();
//     xml.open("POST", "../database/clients_request.php", true);
//     xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//     xml.onreadystatechange = function () {
//         if(xml.readyState === 4 && xml.status === 200) {
//             console.log(xml.responseText);
//             const response = JSON.parse(xml.responseText);
//             if(response.success) {
//                 //Supprimer la ligne de la table si supr réussi
//                 const row = document.querySelector("tr[data-id='${clientId}']");
//                 if (row) row.remove();
//             } else {
//                 alert("Echec de la suppression : " + response.message);
                
//             }
//         }
//     };
//     xml.send(`id=${clientId}`)
// }


function deleteClient(clientId) {
    const xml = new XMLHttpRequest();
    xml.open("POST", "../database/clients_request.php", true);
    xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xml.onreadystatechange = function () {
        if (xml.readyState === 4) {
            console.log("Réponse brute du serveur :", xml.responseText); // Log ici
            if (xml.status === 200) {
                try {
                    const response = JSON.parse(xml.responseText);
                    if (response.success) {
                        const row = document.querySelector(`tr[data-id='${clientId}']`);
                        if (row) row.remove();
                    } else {
                        alert("Echec de la suppression : " + response.message);
                    }
                } catch (error) {
                    console.error("Erreur lors du parsing JSON :", error, xml.responseText);
                }
            }
        }
    };
    xml.send(`id=${clientId}`);
}
