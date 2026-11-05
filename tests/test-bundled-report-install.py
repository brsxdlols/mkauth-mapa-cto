#!/usr/bin/env python3
"""Exercise the real installer and rollback against disposable MK-AUTH directories."""
from pathlib import Path
import os,subprocess,tempfile,sys
root=Path(__file__).resolve().parents[1]
sudo=['sudo'] if '--sudo' in sys.argv else []
def snapshot(path):return {p.relative_to(path).as_posix():p.read_bytes() for p in path.rglob('*') if p.is_file()}
def invoke(script,admin,backup,*args):
    env=dict(os.environ,MKAUTH_ADMIN=str(admin),MKAUTH_BACKUP_ROOT=str(backup))
    cmd=sudo+['env','MKAUTH_ADMIN='+str(admin),'MKAUTH_BACKUP_ROOT='+str(backup),'sh',str(root/'installers'/script),*map(str,args)]
    return subprocess.run(cmd,env=env,check=True,capture_output=True,text=True).stdout
def check(ok,label):
    if not ok:raise AssertionError(label)
    print('PASS',label)
with tempfile.TemporaryDirectory(prefix='mkauth-report-install-') as directory:
    temp=Path(directory).resolve();admin=temp/'admin';addons=admin/'addons';addons.mkdir(parents=True)
    menu=addons/'addon.js';original=b'// unrelated addon\nadd_menu.provedor(\'{"plink":"other/","ptext":"Other"}\');\n'
    menu.write_bytes(original)
    invoke('install.sh',admin,temp/'fresh-backup')
    check((addons/'caixas/VERSION').read_text().strip()=='1.1.53','fresh map installation')
    check((addons/'rel_cto_cad_cli/report-clients.php').is_file(),'fresh report installation')
    check('vertrigo' not in (addons/'rel_cto_cad_cli/config.php').read_text(),'fresh report contains no copied database password')
    text=menu.read_text();check(text.count('addons/rel_cto_cad_cli/')==1 and 'Other' in text,'report menu created without affecting unrelated entries')
    fresh_backup=next((temp/'fresh-backup').iterdir())
    invoke('rollback.sh',admin,temp/'unused',fresh_backup)
    check(not (addons/'caixas').exists() and not (addons/'rel_cto_cad_cli').exists() and menu.read_bytes()==original,'fresh rollback restores original menu and absence of both addons')
    (addons/'caixas').mkdir();(addons/'caixas/old-marker.txt').write_text('old map')
    report=addons/'rel_cto_cad_cli';report.mkdir()
    custom=b'<?php $cfg_port_olt = true; // provider setting\n'
    (report/'config.php').write_bytes(custom);(report/'local.txt').write_text('keep')
    menu.write_bytes(original+b'add_menu.provedor(\'{"plink":"addons/rel_cto_cad_cli/"}\');\nadd_menu.provedor(\'{"plink":"addons/rel_cto_cad_cli/"}\');\n')
    before=snapshot(addons)
    invoke('install.sh',admin,temp/'upgrade-backup')
    check((report/'config.php').read_bytes()==custom and (report/'local.txt').read_text()=='keep','existing report settings and local files preserved')
    check(menu.read_text().count('addons/rel_cto_cad_cli/')==1,'legacy duplicate report shortcuts consolidated')
    first=snapshot(addons)
    invoke('install.sh',admin,temp/'repeat-backup')
    check(menu.read_text().count('addons/rel_cto_cad_cli/')==1 and menu.read_text().count('Mapa Das CTO')==1,'reinstall creates no duplicate shortcuts')
    check((report/'config.php').read_bytes()==custom,'reinstall preserves settings')
    invoke('rollback.sh',admin,temp/'unused',next((temp/'repeat-backup').iterdir()))
    check(snapshot(addons)==first,'reinstall rollback restores both addons and menu byte for byte')
    invoke('rollback.sh',admin,temp/'unused',next((temp/'upgrade-backup').iterdir()))
    check(snapshot(addons)==before,'upgrade rollback restores previous report, map and menu byte for byte')
