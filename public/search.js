// public/js/search.js
$(document).ready(function () {
    var delayTimer;

    $("#searchInput").on("input", function () {
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function () {
            performSearch();
        }, 500); // Adjust the delay time (in milliseconds) as needed
    });

    function performSearch() {
        var searchQuery = $("#searchInput").val();

        $.ajax({
            url: "/search", // Adjust the route name if needed
            type: "POST",
            dataType: "json",
            data: { query: searchQuery },
            success: function (data) {
                updateTable(data);
            },
            error: function (error) {
                console.log("Error:", error);
            }
        });
    }

    function updateTable(results) {
        var tbody = $("table tbody");
        tbody.empty();

        if (results.length > 0) {
            for (var i = 0; i < results.length; i++) {
                var vol = results[i];
                var row = "<tr>" +
                    "<td>" + (vol.pointdepart ? vol.pointdepart : '') + "</td>" +
                    "<td>" + (vol.destination ? vol.destination : '') + "</td>" +
                    "<td>" + formatValue(vol.duree) + "</td>" +
                    "<td>" + formatValue(vol.datedepart) + "</td>" +
                    "<td>" + formatValue(vol.datearrive) + "</td>" +
                    "<td>" + (vol.nbrescale ? vol.nbrescale : '') + "</td>" +
                    "<td>" + (vol.nbrplace ? vol.nbrplace : '') + "</td>" +
                    "<td>" + (vol.classe ? vol.classe : '') + "</td>" +
                    "<td>" + (vol.prix ? vol.prix : '') + "</td>" +
                    "</tr>";
                tbody.append(row);
            }
        } else {
            tbody.append("<tr><td colspan='9'>No records found</td></tr>");
        }
    }

    // Helper function to format values
    function formatValue(value) {
        if (value instanceof Date) {
            return value.toLocaleString(); // Adjust the format as needed
        } else {
            return value || '';
        }
    }
});
