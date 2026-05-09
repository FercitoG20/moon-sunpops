document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Lógica de enfoque y selección automática
    const selectorPaleta = document.getElementById('selectorPaleta');
    const inputCantidad = document.getElementById('inputCantidad');

    // Verificar si venimos de un insert (redirigido por PHP)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('focus') === 'selector') {
        selectorPaleta.focus();
    }

    if(selectorPaleta) {
        selectorPaleta.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if(this.value !== "") {
                document.getElementById("costoProveedor").value = opt.getAttribute("data-costo");
                document.getElementById("inputVenta").value = opt.getAttribute("data-venta");
                
                // Enfocar cantidad y seleccionar el texto para velocidad
                inputCantidad.focus();
                inputCantidad.select();
            } else {
                document.getElementById("costoProveedor").value = "";
                document.getElementById("inputVenta").value = "";
            }
        });
    }

    // 2. Generador de PDF (Rediseño de diseño Premium)
    const btnPdf = document.getElementById('btnExportarPDF');
    if(btnPdf) {
        btnPdf.addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const titulo = document.getElementById('pdfTitulo')?.innerText || 'ORDEN';
            const fecha = document.getElementById('pdfFecha')?.innerText || '';

            // --- CABECERA DE IMPACTO ---
            doc.setFillColor(45, 27, 20); // Choco
            doc.rect(0, 0, 210, 45, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(26);
            doc.text("MOON & SUN POPS", 15, 20);
            
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.text("DOCUMENTO DE CARGA E INVERSIÓN OPERATIVA", 15, 28);
            
            // Detalles Oro
            doc.setTextColor(212, 163, 115);
            doc.setFontSize(14);
            doc.text(`LOTE: ${titulo.toUpperCase()}`, 15, 38);
            doc.setFontSize(12);
            doc.text(`FECHA: ${fecha}`, 160, 38);

            // Datos de la tabla
            const filas = document.querySelectorAll('.pdf-data-row');
            let dataTable = [];
            let totalPz = 0;
            let totalInv = 0;

            filas.forEach(f => {
                const sabor = f.querySelector('.pdf-sabor').innerText;
                const cant = f.querySelector('.pdf-cantidad').innerText;
                const costo = f.querySelector('.pdf-costo').innerText;
                const inv = f.querySelector('.pdf-inversion').innerText;

                totalPz += parseInt(cant);
                totalInv += parseFloat(inv.replace('$', '').replace(',', ''));
                dataTable.push([sabor, costo, cant, inv]);
            });

            // --- TABLA FORMAL ---
            doc.autoTable({
                startY: 55,
                head: [['DESCRIPCIÓN DEL PRODUCTO', 'COSTO U.', 'CANTIDAD', 'SUBTOTAL']],
                body: dataTable,
                theme: 'grid',
                styles: { font: 'helvetica', fontSize: 10, cellPadding: 4 },
                headStyles: { fillColor: [45, 27, 20], textColor: [255, 255, 255], halign: 'center', fontStyle: 'bold' },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { halign: 'center' },
                    2: { halign: 'center' },
                    3: { halign: 'right', fontStyle: 'bold' }
                }
            });

            // --- CUADRO DE RESUMEN ---
            const finalY = doc.lastAutoTable.finalY + 10;
            doc.setDrawColor(188, 108, 37);
            doc.setLineWidth(1);
            doc.setFillColor(254, 250, 224);
            doc.rect(130, finalY, 65, 25, 'FD');

            doc.setTextColor(45, 27, 20);
            doc.setFontSize(10);
            doc.setFont("helvetica", "bold");
            doc.text(`UNIDADES:`, 135, finalY + 10);
            doc.setFont("helvetica", "normal");
            doc.text(`${totalPz} pz`, 188, finalY + 10, { align: 'right' });

            doc.setFontSize(14);
            doc.setFont("helvetica", "bold");
            doc.text(`TOTAL:`, 135, finalY + 20);
            doc.text(`$${totalInv.toLocaleString('en-US', {minimumFractionDigits: 2})}`, 188, finalY + 20, { align: 'right' });

            // Firma y Pie
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text("Reporte generado por Sistema Moon & Sun Pops. Todos los derechos reservados.", 105, 285, { align: "center" });

            doc.save(`Pedido_${titulo.replace(/\s+/g, '_')}.pdf`);
        });
    }
});