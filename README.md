
---

```markdown
# 📊 Consultoría Contable — Sistema de Gestión

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)

Solución integral para la administración y automatización de procesos dentro de una consultoría contable. Este sistema optimiza el flujo de trabajo entre administradores, asesores y clientes a través de una plataforma centralizada.

---

## ✨ Funcionalidades Principales

* 🔐 **Autenticación Segura:** Registro e inicio de sesión con control de accesos basado en roles.
* 📅 **Gestión de Citas:** Programación, modificación y cancelación de asesorías en tiempo real.
* 📂 **Repositorio Documental:** Subida, almacenamiento y descarga de archivos fiscales y contables.
* 💳 **Control de Pagos:** Registro, historial y seguimiento de estados de pago de los servicios.
* 💬 **Comunicación Interna:** Mensajería directa entre usuarios y sistema de notificaciones.
* 📈 **Paneles de Control (Dashboard):** Resumen visualizado de declaraciones, tareas pendientes y estado de cuenta según el rol.

---

## 👥 Roles y Permisos

| Rol | Descripción y Alcance |
| :--- | :--- |
| **👑 Administrador** | Control total del sistema, gestión global de usuarios, asignación de asesores y reportes ejecutivos. |
| **💼 Asesor** | Gestión directa de su cartera de clientes asignados, control de citas, revisión de documentos y servicios. |
| **👤 Cliente** | Acceso a su perfil personal, visualización del dashboard financiero, carga de documentos y consulta de pagos. |

---

## 📂 Estructura del Proyecto

```text
consultoria-contable/
├── backend/
│   ├── config/          # Conexión a BD y configuración general
│   ├── middleware/      # Autenticación y control de roles (RBAC)
│   ├── modules/         # Módulos API (auth, citas, documentos, pagos, etc.)
│   ├── services/        # Servicios compartidos (ej. NotificationService)
│   ├── uploads/         # Almacenamiento local de archivos de usuario
│   └── utils/           # Helper functions (respuestas estandarizadas, validadores)
├── database/
│   ├── schema.sql       # Estructura e índices de la base de datos
│   └── seed.sql         # Set de datos de prueba para desarrollo
├── docker/              # Entorno aislado de desarrollo (PHP / Servidor)
├── frontend/
│   ├── css/             # Estilos de la interfaz de usuario
│   ├── js/
│   │   ├── api/         # Consumo de endpoints del backend
│   │   ├── helpers/     # Manejadores comunes (logout, tokens)
│   │   ├── utils/       # Validaciones dinámicas (sessionCheck)
│   │   └── views/       # Controladores de lógica por vista (dashboards, pagos)
│   └── pages/           # Vistas HTML organizadas jerárquicamente por rol
├── docker-compose.yml   # Orquestador de contenedores
└── structure.txt        # Mapa del proyecto

```

---

## 🛠️ Requisitos del Sistema

Antes de comenzar, asegúrate de cumplir con las siguientes herramientas instaladas:

* **PHP:** Versión 8.0 o superior
* **Gestor de BD:** MySQL o MariaDB
* **Contenedores (Opcional):** Docker y Docker Compose
* **Navegador:** Cualquier navegador web moderno compatible con ECMAScript 6+

---

## 🚀 Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd consultoria-contable

```

### 2. Configurar la Base de Datos

* Importa el archivo `database/schema.sql` en tu servidor MySQL para desplegar la estructura.
* *(Opcional)* Importa `database/seed.sql` si requieres usuarios y registros de prueba para validar el sistema.

### 3. Establecer las credenciales de conexión

Modifica el archivo de configuración con los parámetros de tu entorno local:

```bash
nano backend/config/conexion.php

```

### 4. Inicializar el Servidor

#### Opción A: Servidor embebido de PHP

Ejecuta el siguiente comando apuntando al entorno del backend:

```bash
php -S localhost:8000 -t backend

```

#### Opción B: Despliegue con Docker 🐳

Si prefieres un entorno preconfigurado y aislado, levanta los contenedores con:

```bash
docker-compose up -d

```

### 5. Acceso a la aplicación

Abre tu navegador preferido e ingresa a la pantalla de autenticación:

```text
http://localhost:8000/frontend/pages/auth/login.html

```

*(Nota: Ajusta el puerto e IP si estás utilizando una configuración de Docker diferente).*

---

## 📄 Licencia

Este software es propiedad exclusiva y de **uso interno** para la consultoría contable. Queda prohibida su distribución, copia o comercialización externa sin autorización expresa.