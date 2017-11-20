# Scripts for the Knora API

These are utilities to communicate with the Knora API. Feel free to add more scripts that are useful for the public and document it here.

# General

General Knora scripts.

## Ark

Shows the Knora persistent identifier (ARK identifier) of a given project and resource id.

### Usage

Language: PHP CLI
Usage: `php get_ark_by_resource_id.php -project_id [int] -resource_id [int] -mode ["json"|"default"]`

Params:
- project_id: the Knora project id
- resource_id: the Knora resource id
- mode: determines whether the script should return JSON or a readable string

Example: `php get_ark_by_resource_id.php -project_id 14 -resource_id 2126040 -mode json`

# Projects

Project specific scripts.

## LIMC

Gets the Knora resource id from a given LIMC monument id.

### Usage

Language: PHP CLI
Usage: `php get_resource_id_by_monument_id.php -monument_id [int] -mode -mode ["json"|"default"]`

Params:
- monument_id: the LIMC monument id
- mode: determines whether the script should return JSON or a readable string

Example: `php get_resource_id_by_monument_id.php -monument_id 203026 -mode json`
