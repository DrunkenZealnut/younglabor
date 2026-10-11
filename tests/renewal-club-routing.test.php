<?php
require __DIR__.'/support/renewal.php';require __DIR__.'/../includes/WorkPresenter.php';
check(programPath(['kind'=>'club','slug'=>'other-club'])==='work/programs/other-club','additional clubs must have distinct details');
check(programPath(['kind'=>'club','slug'=>'vibecoding-club'])==='club','canonical club route preserved');
echo "PASS distinct club paths\n";
