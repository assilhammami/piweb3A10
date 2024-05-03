document.addEventListener('DOMContentLoaded', function () {
    const sortLinks = document.querySelectorAll('.sortable-column');

    sortLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const column = this.dataset.column;
            const direction = this.dataset.direction;

            // Perform AJAX request
            fetch(`/backoffice/vols/?column=${column}&direction=${direction}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => response.json())
                .then(data => {
                    // Update the table with sorted data
                    updateTable(data.vols);
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Function to update the table with sorted data
    function updateTable(sortedData) {
        const tbody = document.querySelector('tbody');

        // Clear existing rows
        tbody.innerHTML = '';

        // Add new rows based on sorted data
        sortedData.forEach(function(vol) {
            const row = document.createElement('tr');
            // Create and append cells based on your data structure
            // Adjust this part according to your actual data structure

            // Format duree
            const formattedDuree = vol.duree ? new Date(vol.duree).toISOString().substr(11, 8) : '';

            // Format datedepart and datearrive
            const formattedDepart = vol.datedepart ? new Date(vol.datedepart).toLocaleString() : '';
            const formattedArrive = vol.datearrive ? new Date(vol.datearrive).toLocaleString() : '';


            row.innerHTML = `
                <td>${vol.id}</td>
             <td>${formattedDuree}</td>
            <td>${formattedDepart}</td>
            <td>${formattedArrive}</td>
            <td>${vol.nbrescale}</td>
            <td>${vol.nbrplace}</td>
            <td>${vol.classe}</td>
            <td>${vol.destination}</td>
            <td>${vol.pointdepart}</td>
            <td>${vol.prix}</td>
            <td>
                <a href="${vol.showPath}">show</a>
                <a href="${vol.editPath}">edit</a>
            </td>
        `;

            tbody.appendChild(row);
        });
    }
});