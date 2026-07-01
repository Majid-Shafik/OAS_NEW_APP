# Antigravity IDE - Enterprise Filament Portal Specification

Version: 1.0

---

# Project Name

Enterprise Filament Portal

---

# Objective

Build a lightweight Enterprise Portal using Laravel 13 and Filament v5.

The Portal is the central entry point for all enterprise systems.

The Portal must remain simple, modern, fast, and easy to extend.

---

# Technology Stack

- Laravel 13
- Filament v5
- PHP 8.4+
- Livewire 4
- Tailwind CSS 4
- MySQL 8

---

# Main Responsibilities

The Portal is responsible for:

- Public home page
- Display available systems
- Search systems
- Enterprise branding
- Redirect users to systems
- SSO integration
- System management

The Portal MUST NOT manage:

- Users
- Roles
- Permissions
- Notifications
- Business data

Each application remains fully independent.

---

# System Management (Most Important)

The Portal must include a Filament Resource called:

System

Administrator can:

## Create new system

Fields:

- Name
- English Name
- Slug
- Short Description
- URL
- Thumbnail Image
- Icon (Heroicons or uploaded icon)
- Theme Color
- Display Order
- Is Active
- Open In New Tab (optional)

---

## Update system

Administrator can edit every field.

---

## Disable system

Administrator can deactivate any system.

Inactive systems MUST NOT appear on the public portal.

---

## Enable system

Administrator can activate the system again.

---

## Delete system

Soft Delete preferred.

---

## Reorder systems

Administrator can change display order.

---

# System Thumbnail

Every system must have a thumbnail image.

Recommended size:

800 × 450

Displayed as a beautiful card on Portal.

Fallback image if none uploaded.

---

# Public Home Page

NO LOGIN REQUIRED

The homepage contains:

- Organization Logo
- Organization Name
- Search Box
- Responsive System Cards
- Footer

---

# System Card

Every card contains:

- Thumbnail
- Icon
- System Name
- Description
- Open Button

Example:

------------------------------------------------

📷 Thumbnail

📑 Tender Management System

Manage Contracts and Tenders

[ Open System ]

------------------------------------------------

---

# Search

Live search without page refresh.

Search by:

- Name
- Description

---

# Authentication

Support two authentication providers.

## LDAP / Active Directory

For employees.

Use enterprise credentials.

---

## Local Authentication

For consultants.

Email + Password.

---

# Authentication Flow

User opens Portal.

↓

Select System.

↓

Redirect to target system.

↓

Target system decides authentication provider.

↓

LDAP users authenticate using Active Directory.

↓

Local users authenticate using local credentials.

↓

System creates its own local session.

Portal does not create sessions for systems.

---

# Independence of Applications

Each application manages:

- Users
- Roles
- Permissions
- Notifications
- Database

Portal never synchronizes permissions.

---

# Portal Database

Table:

systems

Columns:

- id
- name
- english_name
- slug
- description
- url
- thumbnail
- icon
- color
- display_order
- is_active
- open_in_new_tab
- created_at
- updated_at
- deleted_at

---

# UI Requirements

Simple.

Modern.

Responsive.

RTL.

Dark Mode.

Light Mode.

Large Cards.

Rounded Corners.

Beautiful Hover Effects.

Fast Loading.

Minimal Design.

---

# Filament Admin Panel

Create complete CRUD for Systems.

Features:

- Search
- Filters
- Sorting
- Upload Thumbnail
- Toggle Active
- Drag & Drop Ordering (preferred)

---

# Home Page Behavior

Only active systems are displayed.

Ordered by Display Order.

Cards must be responsive.

---

# Coding Standards

- Laravel Best Practices
- Clean Code
- Small Controllers
- Service Classes when necessary
- Form Requests
- Policies
- Soft Deletes
- Resource Classes
- Type Hinting
- Strict Types

---

# Future Ready

Architecture must allow future additions:

- Statistics
- Categories
- Favorites
- Recently Used Systems
- Usage Analytics

WITHOUT changing existing code.

---

# Deliverables

Antigravity IDE should generate:

- Laravel 13 project
- Filament v5
- Public Portal
- Filament Admin
- System CRUD
- Image Upload
- Search
- Responsive Cards
- Dark Mode
- RTL
- Clean Architecture
- Production-ready code