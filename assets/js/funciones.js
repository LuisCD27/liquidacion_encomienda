async function cargarOficinaInfo(selectId) {
  const sel = document.getElementById(selectId);
  const oficinaId = sel.value;

  // Limpiar campos si no hay selección
  if (!oficinaId) {
    document.getElementById('of_direccion').value = '';
    document.getElementById('of_telefono1').value = '';
    document.getElementById('of_telefono2').value = '';
    document.getElementById('le_serie').value = '';
    document.getElementById('le_numero').value = '';
    return;
  }

  try {
    // 1️⃣ Obtener la información de la oficina
    const res = await fetch(`../controllers/OficinaController.php?action=info&id=${oficinaId}`);
    if (!res.ok) throw new Error('Error al obtener datos de la oficina');
    const data = await res.json();

    document.getElementById('of_direccion').value = data.direccion || '';
    document.getElementById('of_telefono1').value = data.telefono1 || '';
    document.getElementById('of_telefono2').value = data.telefono2 || '';
    document.getElementById('le_serie').value = data.serie || '';

    // 2️⃣ Obtener el correlativo siguiente desde EncomiendaController
    const res2 = await fetch(`../controllers/EncomiendaController.php?action=siguiente&id=${oficinaId}`);
    if (!res2.ok) throw new Error('Error al obtener número de correlativo');
    const d2 = await res2.json();

    // ✅ Tu controlador devuelve { "serie": "005", "numero": 1 }
    document.getElementById('le_numero').value = d2.numero || 1;

    // Por si la serie viene del segundo controlador (en caso de que falte en OficinaController)
    if (d2.serie && !data.serie) {
      document.getElementById('le_serie').value = d2.serie;
    }

  } catch (err) {
    console.error('❌ Error al cargar información de oficina:', err);
    // Mostrar valores seguros por defecto
    document.getElementById('le_serie').value = '';
    document.getElementById('le_numero').value = '1';
  }
}

