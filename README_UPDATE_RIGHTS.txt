Mopedgarage – Rechte/ACP-Sichtbarkeit

Enthalten:
- eigene Rechte-Kategorie "Mopedgarage" in der phpBB-Rechteverwaltung
- Rechte:
  - u_mopedgarage_view
  - u_mopedgarage_use
  - a_mopedgarage_manage
- Standardvergabe per Migration:
  - Registrierte Benutzer: view + use
  - Administratoren: manage
- ACP/UCP-Module auf die neuen Rechte umgestellt
- ACP-Links der Erweiterung werden per Migration nachgezogen/reaktiviert

Einspielen:
1. Dateien in ext/bruno/mopedgarage ersetzen
2. Im ACP Cache leeren
3. Erweiterung kurz deaktivieren/aktivieren ODER Migration laufen lassen
4. Danach im ACP unter Berechtigungen sollte eine eigene Registerkarte/Kategorie "Mopedgarage" sichtbar sein
5. Im ACP unter Erweiterungen > Mopedgarage sollten Einstellungen und Zusatzfelder sichtbar sein
