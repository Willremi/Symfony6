let links = document.querySelectorAll('[data-delete]');

// Boucle sur les liens
for(let link of links) {
    // On met un écouteur d'événements
    link.addEventListener('click', function(e) {
        e.preventDefault();

        // Demande de confirmation
        if(confirm("Voulez-vous supprimer cette image")) {
            // Envoi de la requête AJAX
            fetch(this.getAttribute("href"), {
                method: "DELETE", 
                headers: {
                    "X-Requested-With": "XMLHttpRequest", 
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({"_token": this.dataset.token})
            }).then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.parentElement.remove()
                } else {
                    alert(data.error)
                }
            })
        }
    });
}