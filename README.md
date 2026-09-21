# WildFile

PHP file storage library with MySQL metadata and redundant checksums.

WildFile provides a simple and reliable way to store files on the filesystem while keeping their metadata and integrity information in MySQL.

## Features

* File storage outside the database
* MySQL-based file metadata
* Redundant checksums for file integrity
* Unique storage paths for uploaded files
* File metadata such as name, size, MIME type and timestamps
* Separation between physical file storage and application metadata
* PHP-based and easy to integrate into existing applications

## Why WildFile?

Storing uploaded files directly in a database is often unnecessary and can make backups and file handling more complicated.

WildFile uses a different approach:

```text
                  ┌──────────────────┐
                  │    Application   │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │     WildFile     │
                  └───────┬────┬─────┘
                          │    │
              ┌───────────┘    └───────────┐
              ▼                            ▼
      ┌────────────────┐          ┌────────────────┐
      │  File Storage  │          │     MySQL      │
      │                │          │    Metadata    │
      │  Actual files  │          │   Checksums    │
      └────────────────┘          └────────────────┘
```

The actual file contents remain in the filesystem, while MySQL stores the information required to identify, manage and verify the files.

## Storage

WildFile does not require files to be stored inside MySQL.

Instead, files are stored in a dedicated storage directory. The storage layer is responsible for mapping a stored file to its physical location.

This allows the application to work with files without exposing the physical storage structure directly.

## Integrity

WildFile stores redundant checksum information for stored files.

Checksums can be used to detect files that have been modified or corrupted after they were originally stored.

This is particularly useful for:

* Detecting accidental file corruption
* Verifying files after backups or migrations
* Detecting unexpected changes to stored files
* Maintaining confidence in long-term file storage

## MySQL metadata

WildFile keeps file-related metadata in MySQL rather than relying solely on the filesystem.

This makes it possible to query and manage stored files using normal database operations while keeping potentially large file contents out of the database.

Typical metadata can include information such as:

* Original filename
* File size
* MIME type
* Storage information
* Checksums
* Creation and modification information

## Backup considerations

A WildFile installation consists of both:

1. The WildFile storage directory
2. The MySQL metadata database

**Both must be included in backups.**

Restoring only the database without the corresponding storage files, or only the storage files without the database, may result in an incomplete file repository.
