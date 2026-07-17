# Entorno de Desarrollo y Producción

Este archivo contiene información sobre los entornos de desarrollo local y de producción para el proyecto **goinvesting**.

## 💻 Entorno de Desarrollo Local
- **Plataforma**: Windows con subsistema de Linux para Windows (**WSL**).
- **Ruta del Proyecto**: `/home/bonilla/Projects/goinvesting` (accedido vía `\\wsl.localhost\Ubuntu\home\bonilla\Projects\goinvesting`).
- **Comandos**: Ejecutar comandos de Git y consola anteponiendo `wsl` si se ejecutan desde el host (ej. `wsl git status`).

## 🚀 Entorno de Producción
- **Servidor (IP)**: `75.119.136.151` (`goinvesting.es`)
- **Usuario SSH**: `developer`
- **Ruta del Proyecto**: `/home/developer/Projects/goinvesting`
- **Entorno de Contenedores**: **DDEV**
- **Comandos Útiles**:
  - Actualizar código: `ssh developer@75.119.136.151 "cd /home/developer/Projects/goinvesting && git pull origin main"`
  - Ejecutar Artisan en producción: `ssh developer@75.119.136.151 "cd /home/developer/Projects/goinvesting && ddev exec php artisan <comando>"`
