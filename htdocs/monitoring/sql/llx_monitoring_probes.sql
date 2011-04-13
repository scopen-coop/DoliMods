-- ===================================================================
-- Copyright (C) 2010-2011 Laurent Destailleur <eldy@users.sourceforge.net>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- $Id: llx_monitoring_probes.sql,v 1.6 2011/04/13 21:06:50 eldy Exp $
-- ===================================================================


CREATE TABLE llx_monitoring_probes 
(
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	title varchar(64) NOT NULL, 
    groupname varchar(64) NULL, 
	url varchar(250) NOT NULL,
    useproxy integer default 0,
    checkkey varchar(250),	
    maxval integer,  
	frequency integer default 60, 
	active integer default 1,
	status integer default 0,
	lastreset datetime,
	oldesterrortext text,
	oldesterrordate datetime
) ENGINE = innodb;

-- lastreset = last date of status change
-- 