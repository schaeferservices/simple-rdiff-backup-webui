# simple-rdiff-backup-webui

Very simple 'read-only' PHP based web UI for [rdiff-backup](https://github.com/rdiff-backup/rdiff-backup).

## Example
![](https://raw.githubusercontent.com/schaeferservices/simple-rdiff-backup-webui/main/example.png)

## Configuration
### File 'config.json'
```json
{
    "BackupDisks": [
        "<backup-disk-1>",
        "<backup-disk-1>",
        ...
    ],
    "BackupDirs": [
        "<backup-folder-1>", 
        "<backup-folder-2>",
        ...
    ]
}
```
Example: [config-example.json](config-example.json)

## Get status as JSON 
**Simply set the GET parameter 'output' to 'json'.**
```
<base-addess>?output=json
```

**Sample response**
```json
{
    "BackupDiskStats": [
        {
            "device": "/dev/sda1",
            "point": "/mnt/backup",
            "spaceSize": "1.8TB",
            "backupSize": "559GB",
            "usage": "33%",
            "state": "Ok",
            "statePic": "✔️"
        },
        ...
    ],
    "BackupStats": [
        {
            "directory": "/mnt/backup/disk1",
            "backups": [
                {
                    "timestamp": "02.04.2021 03:00:02",
                    "type": "current mirror"
                },
                {
                    "timestamp": "30.03.2021 03:00:02",
                    "type": "incremental"
                },
                ...
            ],
            "state": "Ok",
            "statePic": "✔️"
        },
        ...
    ]
}
```

## Use check_backup.py icinga2 check script
### Requirements
* python-2.7.x with *argparse*, *sys* and *requests* enabled

### Arguments
| Argument            | Description
| --------------------|----------------------------------------------------------------------
| `--host` / `-H`     | **Required.** Host the backup web-instance is running on.
| `--port` / `-P`     | Port SSIT Backup is running on (default: 80).
| `--protocol`        | Choose either HTTP or HTTPS. Default: HTTP
| `--disks` / `-D`    | Get status of backup disks. Mutually exclusive to `--backups`
| `--backups` / `-B`  | Get status of backups. Mutually exclusive to `--disks`

### Icinga 2 CheckCommand
```
object CheckCommand "backup" {
    import "plugin-check-command"
    command = [ PluginDir + "/check_backup.py" ]
    arguments += {
        "--protocol" = "$backup_protocol$"
        "--host" = "$backup_host$"
        "--port" = "$backup_port$"
        "--disks" = {
		description = "Status of backup disks"
		set_if = "$backup_disks$"
		}
        "--backups" = {
		description = "Status of backups"
		set_if = "$backup_backups$"
		}
    }
    vars.backup_host = "$address$"
    vars.backup_disks = false
    vars.backup_backups = false
}
```