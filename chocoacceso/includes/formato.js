// Validación de Cédula (Formato Venezolano)
document.querySelector('form').addEventListener('submit', function(e) {
    const cedula = document.querySelector('input[name="cedula"]').value;
    const regex = /^[V|E|J|P][-][0-9]{5,9}$/i; // Formato V-12345678

    if (!regex.test(cedula)) {
        e.preventDefault();
        alert("Formato de cédula inválido. Use el formato: V-00000000");
    }
});