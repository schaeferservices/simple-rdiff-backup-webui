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
            "state": "OK",
            "statePic": "✔️"
        },
        ...
    ],
    "BackupStats": [
        {
            "/mnt/backup/disk1": [
                {
                    "timestamp": "02.04.2021 03:00:02",
                    "type": "current mirror"
                },
                {
                    "timestamp": "30.03.2021 03:00:02",
                    "type": "incremental"
                },
                ...
             ]
        },
        ...
    ]
}
        
```
