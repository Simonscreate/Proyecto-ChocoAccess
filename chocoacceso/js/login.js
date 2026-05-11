document.addEventListener('DOMContentLoaded', () => {
    const accessForm = document.getElementById('accessForm');
    const responseMsg = document.getElementById('responseMsg');

    accessForm.addEventListener('submit', (e) => {
        // En una implementación real con AJAX podrías prevenir el envío:
        // e.preventDefault();
        
        const cedula = document.getElementById('cedula').value;
        
        // Validación básica frontend
        if(cedula.length < 6) {
            e.preventDefault();
            responseMsg.classList.remove('d-none');
            responseMsg.innerHTML = `<span class="text-danger fw-bold">Cédula inválida</span>`;
            return;
        }

        // Efecto visual de carga
        const btn = e.target.querySelector('button');
        btn.innerHTML = 'Procesando...';
        btn.disabled = true;
    });
});