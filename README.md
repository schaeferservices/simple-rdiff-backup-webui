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
