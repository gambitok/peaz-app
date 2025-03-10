# SOFTWARE PORTAL - CYBER INTEL SYSTEMS

## Overview

SOFTWARE PORTAL - CYBER INTEL SYSTEMS is a robust platform developed to support cybersecurity operations. This application is designed for local environments and provides a comprehensive suite of tools to manage and analyze cyber intelligence data.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Scripts](#scripts)
- [Environment Variables](#environment-variables)
- [Dependencies](#dependencies)

## Installation

To set up the project locally, follow these steps:

1. Clone the repository:
    ```bash
    git clone <repository-url>
    cd <repository-directory>
    ```

2. Install the project dependencies:
    ```bash
    npm install
    ```

3. Copy the example environment file and configure the environment variables:
    ```bash
    cp .env.example .env
    ```

4. Generate the application key:
    ```bash
    php artisan key:generate
    ```

## Configuration

The main configuration file for the project is the `.env` file. This file contains all the necessary environment variables to run the application. Ensure that you update this file with the appropriate values for your local environment.

## Usage

To run the application locally, use the following command:

```bash
npm run dev
```

This will start the application in development mode.

## Scripts

The following scripts are available in the project:

- **dev**: Run the application in development mode.
- **development**: Run webpack in development mode.
- **watch**: Watch for file changes and recompile.
- **watch-poll**: Watch for file changes with polling and recompile.
- **hot**: Run webpack-dev-server with hot module replacement.
- **prod**: Run the application in production mode.
- **production**: Run webpack in production mode.

## Dependencies

The project relies on the following dependencies:

- **axios**: Promise-based HTTP client for the browser and node.js.
- **cross-env**: Run scripts that set and use environment variables across platforms.
- **laravel-mix**: An elegant wrapper around Webpack for the 80% use case.
- **lodash**: A modern JavaScript utility library delivering modularity, performance, and extras.
- **resolve-url-loader**: Resolves relative paths in url() statements based on the original source file.
- **sass**: CSS with superpowers.
- **sass-loader**: Loads a Sass/SCSS file and compiles it to CSS.

