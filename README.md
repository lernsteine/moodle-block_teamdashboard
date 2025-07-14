# Team Dashboard Block for Moodle

The **Team Dashboard** block provides a quick overview for non-editing trainers to monitor course progress of their group participants.

## ✨ Features

- Shows course progress (Completed / In progress / Overdue)
- Group-aware visibility: trainers only see participants from their assigned groups (if group mode is active)
- Simple, responsive bar chart display
- Localized in English and German
- No personal data is stored

## 📦 Installation

1. Copy or clone this plugin into the `blocks/` directory of your Moodle installation:
   ```bash
   git clone https://github.com/youraccount/moodle-block_teamdashboard.git blocks/teamdashboard
   ```
2. Log in to your Moodle site as admin.
3. Follow the upgrade process.

## 🔐 Privacy

This plugin does **not** store any personal user data. It implements Moodle's Privacy API as a `null_provider`.

## 🖼 Screenshots

![Dashboard Example](screenshots/dashboard_example.png)

## 🧩 Compatibility

Tested with Moodle 4.5 and 4.6. Requires course completion tracking to be enabled.

## 📄 License

This plugin is licensed under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).  
Copyright (C) 2024.

Developed and maintained by [Your Name or Institution].

---

We welcome contributions and feedback!
