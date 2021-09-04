# [Alpha] Project Overhaul

Project Overhaul is a new web application framework specifically designed for Eve Online applications. It replaces the *Access Control* framework that was used in my early projects with a much more robust object-oriented design. 

This framework features built-in user authentication and access control that can be altered in-app, using either Eve Online Affiliations (Character, Corporation, Alliance) or Brave NeuCore Groups, with these different methods being interchangeable with a simple update of a config value. It also features an easy to use logging interface and a robust, in-app view and filter for log entries. 

This framework is currently in Alpha. It works, but may contain bugs, and much of the functionality is not yet documented. It also still needs some optimization to improve load times. 

## Requirements

The core of this framework requires the following: 

* Apache ≥ 2.4
  * The `DocumentRoot` config option to set `/public`
  * The `FallbackResource` config option set to `/index.php`
* PHP ≥ 8.0
  * The `curl` Built-In Extension
  * The `pdo_mysql` Built-In Extension
  * The `openssl` Built-In Extension
* An SQL Server
  * If you are using MySQL, the Authentication Method **MUST** be the Legacy Version. PDO does not support the use of `caching_sha2_password` Authentication. 
* A Registered Eve Online Application. 
  * This can be setup via the [Eve Online Developers Site](https://developers.eveonline.com/).
* [When Using The Neucore Authentication Method] A Neucore Application
  * The application needs the `app-chars` and `app-groups` roles added, along with any groups that you want to be able to set access roles for.

## The Basics

### Functionality Goals

### File Structure

### Expandability

## Initial Configuration

### Config File

### Database

### Authentication

#### Custom Access Roles

### Logging

#### Custom Log Types

## Creating Pages

### Registering a Page

### View Classes

### Model Classes

### Controller Classes

### API Classes

### Local Resources
