async function fetchCorredores() {
    const php = "https://ambuvirtual.com/corredores.php";

    try {
        const response = await fetch(php);
        const data = await response.json();

        if (Array.isArray(data)) {
            // Obtener la referencia a los elementos de la tabla
            const tableHeader = document.getElementById("tableHeader");
            const tableBody = document.getElementById("tableBody");

            // Limpiar tabla existente
            tableHeader.innerHTML = "";
            tableBody.innerHTML = "";

            if (data.length > 0) {s
                // Crear encabezados de tabla
                const headers = Object.keys(data[0]);
                headers.forEach((header) => {
                    const th = document.createElement("th");
                    th.textContent = header;
                    tableHeader.appendChild(th);
                });

                // Crear filas de tabla
                data.forEach((row) => {
                    const tr = document.createElement("tr");
                    headers.forEach((header) => {
                        const td = document.createElement("td");
                        td.textContent = row[header];
                        tr.appendChild(td);
                    });
                    tableBody.appendChild(tr);
                });
            } else {
                const tr = document.createElement("tr");
                const td = document.createElement("td");
                td.colSpan = headers.length;
                td.textContent = "No hay datos disponibles";
                tr.appendChild(td);
                tableBody.appendChild(tr);
            }
        } else {
            Swal.fire({
                title: "Error",
                text: "Error al cargar los datos.",
                icon: "error",
            });
        }
    } catch (error) {
        console.error("Error al cargar los datos:", error);
        Swal.fire({
            title: "Error",
            text: "Hubo un problema al cargar los datos.",
            icon: "error",
        });
    }
}

// Llamar a la función para cargar los datos cuando la página se carga
document.addEventListener("DOMContentLoaded", fetchCorredores);
