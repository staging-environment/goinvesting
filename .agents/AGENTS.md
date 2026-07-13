# Production Environment Credentials

The credentials and connection information for the production environment of the **goinvesting** project are:

- **Hostname / IP Address**: `75.119.136.151` (`goinvesting.es`)
- **SSH User**: `developer` (or `root`)
- **SSH Key**: Configured in local host profile `pinomontano` or `andara` (uses standard key `~/.ssh/id_rsa`).
- **Project Directory**: `/home/developer/Projects/goinvesting`
- **Docker Environment**: DDEV. Run commands inside the DDEV containers as the `developer` user:
  ```bash
  ssh developer@75.119.136.151 "cd /home/developer/Projects/goinvesting && ddev exec php artisan <command>"
  ```
