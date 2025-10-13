# 📦 Fase 2: "Solicitar Eliminación" (Futuro)

## Estado Actual
✅ **Fase 1 Completada:**
- Borrado lógico con estado `'hidden'`
- Edición ilimitada con trace completo
- UI con modal transparente
- Badge para psicólogo con metadata

---

## Implementación Futura (si se requiere)

### Estados adicionales a agregar:
```php
'deletion_requested' => Paciente solicitó eliminación (pendiente)
'deletion_approved' => Psicólogo aprobó (oculto para ambos)
'deletion_rejected' => Psicólogo rechazó (vuelve a activo)
```

### Meta fields preparados:
```php
_deletion_requested_at => timestamp
_deletion_requested_by => user_id
_deletion_approved_by => user_id (psicólogo)
_deletion_approved_at => timestamp
_deletion_rejection_reason => text
```

### Archivos a modificar:

#### 1. **ResponseController.php** (+60 líneas)
```php
public static function requestDeletion() {
    // Cambiar estado a 'deletion_requested'
    // Notificar al psicólogo
}

public static function approveDeletion() {
    // Verificar que current_user es psicólogo
    // Cambiar estado a 'deletion_approved'
    // Notificar al paciente
}

public static function rejectDeletion() {
    // Cambiar estado de vuelta a '1'
    // Guardar razón del rechazo
}
```

#### 2. **Dashboard Psicólogo** (nueva sección)
```php
// Panel de solicitudes pendientes
<div class="pending-deletions">
    <h3>Solicitudes de eliminación pendientes</h3>
    <!-- Lista de comentarios con 'deletion_requested' -->
    <!-- Botones: Aprobar / Rechazar -->
</div>
```

#### 3. **JavaScript** (+40 líneas)
```javascript
handleRequestDeletion(responseId) {
    // Modal explicativo
    // AJAX a openmind_request_deletion
}
```

#### 4. **UI Paciente** (botón adicional)
```html
<button class="btn-request-deletion">
    Solicitar eliminación
</button>
```

---

## Estimación de Tiempo

| Tarea | Horas |
|-------|-------|
| Backend (Controller methods) | 2h |
| Panel psicólogo | 3h |
| UI/UX copy | 1h |
| Testing | 1h |
| **TOTAL** | **7h** |

---

## Decisión Trigger

**Implementar solo si:**
- 3+ psicólogos solicitan la funcionalidad
- Feedback de usuarios indica necesidad real
- Cliente específico lo requiere contractualmente

**No implementar si:**
- Ningún usuario pregunta por esto en 6 meses
- "Ocultar" satisface el 100% de casos de uso

---

## Notas de Arquitectura

✅ Base de datos ya preparada (acepta estados custom)  
✅ Meta fields compatibles  
✅ Queries escalables  
✅ No requiere migrations

**Complejidad:** Baja (sistema ya diseñado para esto)  
**Riesgo:** Ninguno (solo agregar, no modificar existente)