<?php

DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_3');
DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_4');
DropConstraint('internalstockcatrole', 'secroleid');

DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_3');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_4');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_5');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_6');

DropConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_3');
DropConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_4');

DropConstraint('stockrequest', 'stockrequest_ibfk_3');
DropConstraint('stockrequest', 'stockrequest_ibfk_4');

DropConstraint('stockrequestitems', 'dispatchid');
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_3');
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_4');

DropConstraint('workorders', 'worksorders_ibfk_1');

UpdateDBNo(basename(__FILE__, '.php'));

?>