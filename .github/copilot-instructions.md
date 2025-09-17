# Copilot Instructions for AI Agents

## Project Overview
This repository contains example scripts, primarily focused on infrastructure automation using Ansible. The main script automates Docker installation and Portainer deployment on Ubuntu systems.

## Key Components
- `Ansible/install_docker_with_portainer.yml`: Ansible playbook to install Docker and run Portainer. It:
  - Installs required system packages
  - Adds Docker's official GPG key and repository
  - Installs Docker CE
  - Starts and enables the Docker service
  - Creates a Docker volume for Portainer data
  - Runs the Portainer container with required volumes and port mapping
- `test.php`: Example PHP script (purpose not documented; inspect for details if relevant)

## Developer Workflows
- **Ansible Playbook Execution:**
  - Run the playbook with:
    ```sh
    ansible-playbook -i <inventory> Ansible/install_docker_with_portainer.yml
    ```
  - Requires root privileges (`become: true` is set)
  - Target hosts must be Ubuntu-based and have Python installed for Ansible
- **No build or test automation detected.**
  - No CI/CD, Makefile, or test runner conventions found

## Project-Specific Patterns
- All infrastructure logic is centralized in the Ansible playbook
- Uses Docker's official repository and GPG key for secure installation
- Portainer is run as a Docker container with persistent data volume
- No custom Ansible roles or collections; all tasks are inline

## Integration Points
- External dependencies: Docker CE, Portainer CE
- No detected cross-component communication beyond Docker/Portainer setup

## Conventions
- Scripts are kept simple and self-contained
- No advanced Ansible features (roles, templates, variables) used
- All configuration is hardcoded for Ubuntu (focal)

## Recommendations for AI Agents
- When adding infrastructure automation, follow the inline task pattern in the existing playbook
- Reference official sources for package installation and container images
- Document any new scripts in the README
- If expanding to other OSes or using Ansible roles, update this file and the README with new conventions

## Example: Adding a New Dockerized Service
To add a new service via Ansible, append a `docker_container` task similar to the Portainer example, specifying image, ports, and volumes as needed.

---
If any conventions or workflows are unclear, please request clarification or examples from the repository owner.
