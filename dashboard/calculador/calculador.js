document.addEventListener('DOMContentLoaded', () => {
    
    const selectorPaleta = document.getElementById('selectorPaleta');
    if(selectorPaleta) {
        selectorPaleta.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if(this.value !== "") {
                document.getElementById("costoProveedor").value = opt.getAttribute("data-costo");
                document.getElementById("inputVenta").value = opt.getAttribute("data-venta");
                document.getElementById("inputCantidad").focus();
            }
        });
    }

    const btnPdf = document.getElementById('btnExportarPDF');
    if(btnPdf) {
        btnPdf.addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const titulo = document.getElementById('pdfTitulo')?.innerText || 'PEDIDO';
            const fecha = document.getElementById('pdfFecha')?.innerText || '';

            doc.setFillColor(45, 27, 20);
            doc.rect(0, 0, 210, 40, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(22);
            doc.text("MOON & SUN POPS", 15, 20);
            
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.text("SISTEMA DE CONTROL DE INVENTARIO Y COSTOS", 15, 28);
            
            doc.setTextColor(212, 163, 115);
            doc.setFontSize(12);
            doc.text(`LOTE: ${titulo.toUpperCase()}`, 15, 35);
            doc.text(`FECHA: ${fecha}`, 160, 35);
            const filas = document.querySelectorAll('.pdf-data-row');
            let data = [];
            let totalPz = 0;
            let totalInv = 0;

            filas.forEach(f => {
                const sabor = f.querySelector('.pdf-sabor').innerText;
                const cant = f.querySelector('.pdf-cantidad').innerText;
                const costo = f.querySelector('.pdf-costo').innerText;
                const inv = f.querySelector('.pdf-inversion').innerText;

                totalPz += parseInt(cant);
                totalInv += parseFloat(inv.replace('$', '').replace(',', ''));
                data.push([sabor, costo, cant, inv]);
            });

            doc.autoTable({
                startY: 50,
                head: [['SABOR / PRODUCTO', 'COSTO UNIT.', 'CANTIDAD', 'INVERSIÓN TOTAL']],
                body: data,
                theme: 'grid',
                styles: { font: 'helvetica', fontSize: 9, cellPadding: 3 },
                headStyles: { fillColor: [78, 52, 46], textColor: [255, 255, 255], halign: 'center' },
                columnStyles: {
                    0: { cellWidth: 80 },
                    1: { halign: 'center' },
                    2: { halign: 'center' },
                    3: { halign: 'right', fontStyle: 'bold' }
                }
            });

            const finalY = doc.lastAutoTable.finalY + 10;
            doc.setDrawColor(188, 108, 37);
            doc.setFillColor(254, 250, 224);
            doc.rect(120, finalY, 75, 25, 'FD');

            doc.setTextColor(45, 27, 20);
            doc.setFontSize(10);
            doc.text(`TOTAL PIEZAS:`, 125, finalY + 10);
            doc.text(`${totalPz} pz`, 185, finalY + 10, { align: 'right' });

            doc.setFontSize(12);
            doc.setFont("helvetica", "bold");
            doc.text(`TOTAL:`, 125, finalY + 20);
            doc.text(`$${totalInv.toLocaleString('en-US', {minimumFractionDigits: 2})}`, 185, finalY + 20, { align: 'right' });

            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text("Este documento es un reporte generado por el sistema Moon & Sun Pops.", 105, 285, { align: "center" });

            doc.save(`Pedido_${titulo.replace(/\s+/g, '_')}.pdf`);
        });
    }
});