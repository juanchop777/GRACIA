# Matriz de Evaluación ISO/IEC 25010

| Característica | Subcaracterística | Descripción | Nivel de Cumplimiento | Observaciones |
| :--- | :--- | :--- | :---: | :--- |
| **Adecuación funcional** | **Completitud funcional** | El sistema implementa todas las funciones especificadas. | **Medio** | La parte Web (PHP) está completa: registro, login, catálogo, carrito, checkout, panel administrativo y reportes. Sin embargo, la aplicación móvil (Flutter) es aún un proyecto inicial sin funcionalidad implementada. |
| | **Corrección funcional** | Las funciones entregan resultados correctos. | **Alto** | Se implementan transacciones de base de datos (`$pdo->beginTransaction()`) en la creación de pedidos para asegurar la consistencia del inventario y el pedido. El cálculo de totales en el backend parece correcto. |
| | **Pertinencia funcional** | Las funciones son relevantes para los objetivos del usuario. | **Alto** | Las funcionalidades web (catálogo, filtros, gestión de usuarios) son directamente relevantes para un e-commerce. La API (`api/productos.php`) está preparada para dar soporte a aplicaciones externas. |
| **Eficiencia de desempeño** | **Comportamiento temporal** | El sistema responde en tiempos adecuados. | **Medio** | La carga de productos usa paginación opcional (`LIMIT`), pero por defecto algunas consultas podrían traer todos los registros. No se observa uso de caché para consultas frecuentes ni para imágenes. |
| | **Utilización de recursos** | Uso eficiente de CPU, memoria, red, etc. | **Medio** | Uso estándar de recursos. Las imágenes se sirven directamente sin compresión o CDN. |
| | **Capacidad** | Manejo de volumen de usuarios o transacciones. | **Medio** | Adecuado para una PYME. La base de datos relacional soportará un volumen moderado. Para alta concurrencia se requeriría optimizar consultas. |
| **Compatibilidad** | **Coexistencia** | Puede funcionar con otros sistemas sin interferencias. | **Alto** | Al ser un sistema web basado en estándares (LAMP/WAMP stack), coexiste bien en servidores compartidos. |
| | **Interoperabilidad** | Intercambia información con otros sistemas fácilmente. | **Alto** | Dispone de una API REST (JSON) en la carpeta `/api`, permitiendo la integración con la app móvil u otros sistemas externos. |
| **Usabilidad** | **Reconocibilidad de adecuación** | Es fácil entender si el sistema es útil. | **Alto** | La interfaz web cuenta con un diseño claro que comunica inmediatamente el propósito de venta de zapatos. |
| | **Aprendizabilidad** | Es fácil aprender a usar el sistema. | **Alto** | Flujos estándares de e-commerce (Home -> Producto -> Carrito -> Checkout). Panel administrativo intuitivo. |
| | **Operabilidad** | Es fácil de usar en la práctica. | **Medio** | La interfaz web es responsiva. Faltan características avanzadas como búsqueda predictiva. |
| | **Protección frente a errores de usuario** | Minimiza errores del usuario o los corrige. | **Medio** | Validaciones básicas en servidor. Falta validación en cliente (JS) para feedback inmediato. |
| | **Involucración del usuario** | Considera las necesidades del usuario. | **Alto** | Incluye secciones de "Nosotros" y "Contacto", generando confianza. |
| | **Inclusividad** | Accesible para diferentes perfiles de usuario. | **Bajo** | No se observan etiquetas ARIA ni atributos de accesibilidad explícitos. |
| | **Asistencia al usuario** | Brinda ayuda en el uso del sistema. | **Medio** | Mensajes de error y éxito claros. |
| | **Autodescriptividad** | Su funcionamiento es evidente o autoexplicativo. | **Alto** | Etiquetas claras en botones y formularios. |
| **Fiabilidad** | **Ausencia de fallos** | Opera sin errores en condiciones normales. | **Medio** | Manejo de excepciones (`try-catch`) en conexión a DB y consultas clave. |
| | **Disponibilidad** | El sistema está disponible cuando se necesita. | **N/A** | Depende del despliegue en servidor. |
| | **Tolerancia a fallos** | Responde adecuadamente ante fallos parciales. | **Medio** | Si falla la DB, muestra mensajes de error controlados. |
| | **Recuperabilidad** | Puede restaurarse tras un fallo. | **Bajo** | No se observa sistema automático de backups. |
| **Seguridad** | **Confidencialidad** | Protege los datos de accesos no autorizados. | **Alto** | Uso de `password_hash` y `password_verify`. Sesiones protegidas. |
| | **Integridad** | Evita alteraciones no autorizadas de la información. | **Alto** | Uso estricto de Sentencias Preparadas (PDO) para prevenir inyección SQL. |
| | **No-repudio** | Garantiza que las acciones no puedan ser negadas. | **Medio** | Se registran movimientos de inventario con ID de usuario y fecha. |
| | **Responsabilidad** | Registra y rastrea acciones del sistema. | **Medio** | La tabla `movimientos_inventario` rastrea quién hizo cambios en stock. |
| | **Autenticidad** | Verifica la identidad de los usuarios. | **Alto** | Login funcional con validación contra base de datos. Roles (Admin/Usuario). |
| | **Resistencia** | Capacidad de resistir ataques o vulnerabilidades. | **Medio** | Protección contra SQL Injection. Protección XSS básica. Falta protección CSRF. |
| **Mantenibilidad** | **Modularidad** | Componentes bien separados y definidos. | **Medio** | Separación básica, aunque `config.php` tiene demasiadas responsabilidades. |
| | **Reusabilidad** | Se pueden reutilizar partes del sistema. | **Alto** | Funciones de DB centralizadas y reutilizadas en backend y API. |
| | **Analizabilidad** | Es fácil de diagnosticar errores y problemas. | **Medio** | Uso de `error_log` ayuda al diagnóstico. |
| | **Capacidad de ser modificado** | Es fácil de actualizar o mejorar. | **Medio** | Código procedural fácil de seguir, pero estructura mejorable. |
| | **Capacidad de ser probado** | Es posible hacer pruebas de forma sencilla. | **Bajo** | No hay pruebas unitarias para PHP. |
| **Portabilidad** | **Adaptabilidad** | Puede adaptarse a distintos entornos. | **Alto** | Web responsiva. Backend estándar PHP/MySQL. |
| | **Escalabilidad** | Puede crecer o reducirse sin afectar la calidad. | **Medio** | Estructura de DB sólida para crecimiento moderado. |
| | **Instalabilidad** | Se instala sin complicaciones. | **Alto** | Incluye script `setup.php` para instalación automática. |
| | **Reemplazabilidad** | Puede ser sustituido por otro sistema fácilmente. | **Medio** | Datos en formato estándar SQL. |
| **Protección** | **Restricción operativa** | Define límites claros de uso seguro. | **Alto** | Roles claramente definidos (Admin vs Usuario). |
| | **Identificación de riesgos** | Evalúa y documenta posibles riesgos del sistema. | **Bajo** | No hay documentación explícita de riesgos. |
| | **Protección ante fallos** | Previene fallos catastróficos o graves. | **Medio** | Manejo de errores de conexión evita exponer credenciales. |
| | **Advertencia de peligro** | Informa al usuario ante posibles errores críticos. | **Medio** | Alertas de UI para errores. |
| | **Integración segura** | Se conecta a otros sistemas de forma segura. | **Medio** | Configuración SMTP externalizada. |
