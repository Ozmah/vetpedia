<?php

declare(strict_types=1);

namespace App\Enums;

enum Ability: string
{
    case AccessInternal = 'access-internal';
    case CreateEntries = 'create-entries';
    case ManageUnapprovedEntries = 'manage-unapproved-entries';
    case ManageApprovedEntries = 'manage-approved-entries';
    case ApproveEntries = 'approve-entries';
    case ArchiveEntries = 'archive-entries';
    case ManageSources = 'manage-sources';
    case ManageSpecies = 'manage-species';
    case ManageCatalogs = 'manage-catalogs';
    case ViewUsers = 'view-users';
    case CreateUsers = 'create-users';
    case CreateAdmins = 'create-admins';
    case ManageUsers = 'manage-users';
    case ManageAdmins = 'manage-admins';
    case SuspendUsers = 'suspend-users';
    case UnsuspendUsers = 'unsuspend-users';
    case RunSearchMaintenance = 'run-search-maintenance';
    case RunBackups = 'run-backups';
    case ViewLocalDatabase = 'view-local-database';
}
