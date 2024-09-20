document.getElementById('runnerForm').addEventListener('submit', async function(event) {
    event.preventDefault(); // Prevenir el envío del formulario por defecto

    // Deshabilitar el botón de enviar
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;

    // Capturar los valores de los inputs
    const numero = document.getElementById('numero').value;
    const nombre = document.getElementById('nombre').value;
    const apellido = document.getElementById('apellido').value;
    const distancia = document.getElementById('distancia').value;
    const edad = document.getElementById('edad').value;
    const sexo = document.getElementById('sexo').value;

    // Crear un objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id', numero);
    formData.append('nombre', nombre);
    formData.append('apellido', apellido);
    formData.append('distancia', distancia);
    formData.append('edad', edad);
    formData.append('sexo', sexo);

    // Definir mensajes de error según el código de error recibido
    const errorMessages = {
        400: 'La distancia proporcionada no es válida.',
        401: 'La edad proporcionada no se encuentra en un rango válido.',
        404: 'No existe una categoría para los datos proporcionados.',
        409: 'El Número de Corredor proporcionado ya está registrado.',
        500: 'Error de servidor. Por favor, inténtelo de nuevo más tarde.'
    };

    try {
        // Realizar la solicitud POST al archivo PHP
        const response = await fetch("https://ambuvirtual.com/process.php", {
            method: 'POST',
            mode: 'cors',
            body: formData
        });

        // Verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        // Parsear la respuesta JSON
        const result = await response.json();

        if (result.status === 'success') {
            Toastify({
                text: result.message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#4caf50",
            }).showToast();
        } else {
            // Obtener el mensaje de error basado en el código
            const errorMessage = errorMessages[result.code] || 'Error desconocido.';
            Toastify({
                text: `${errorMessage} (Código de error: ${result.code})`,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "red", // Color rojo para errores
                close: true,
            }).showToast();
        }
    } catch (error) {
        console.error('Error al enviar los datos:', error);

        Toastify({
            text: 'Hubo un problema al enviar los datos. Por favor, inténtelo de nuevo.',
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "red",
            close: true,
        }).showToast();
    } finally {
        // Habilitar el botón de enviar nuevamente
        submitBtn.disabled = false;
    }
});

// Función para cargar el ranking
async function loadRanking() {
    try {
        const response = await fetch('https://ambuvirtual.com/corredores.php', {
            method: 'POST',
            mode: 'cors',
            body: new URLSearchParams({
                accion: 'ranking',
                sexo: sexoSelect.value,
                categoria: categoriaSelect.value,
                edad_min: edadMinInput.value,
                edad_max: edadMaxInput.value
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        displayRanking(data);

        Toastify({
            text: 'Ranking cargado exitosamente',
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "green",
            close: true,
        }).showToast();
    } catch (error) {
        console.error('Error al cargar el ranking:', error);

        Toastify({
            text: 'Hubo un problema al cargar el ranking. Por favor, inténtelo de nuevo.',
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "red",
            close: true,
        }).showToast();
    }
}

// Función para mostrar el ranking en la tabla
function displayRanking(data) {
    rankingTable.innerHTML = ''; // Limpiar tabla actual

    if (data.length === 0) {
        rankingTable.innerHTML = '<tr><td colspan="6">No se encontraron corredores.</td></tr>';
        return;
    }

    data.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.nombre}</td>
            <td>${item.apellido}</td>
            <td>${item.distancia} km</td>
            <td>${item.tiempo}</td>
            <td>${item.sexo}</td>
        `;
        rankingTable.appendChild(row);
    });
}
