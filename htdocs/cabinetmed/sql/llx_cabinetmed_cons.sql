-- ============================================================================
-- Copyright (C) 2011 Laurent Destailleur  <eldy@users.sourceforge.net>
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
-- $Id: llx_cabinetmed_cons.sql,v 1.4 2011/05/25 15:19:51 eldy Exp $
-- ===========================================================================

-- DROP TABLE llx_cabinetmed_cons
CREATE TABLE llx_cabinetmed_cons (
  rowid              integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc             integer,
  fk_user            integer,
  datecons           date NOT NULL,
  typepriseencharge  varchar(8),
  motifconsprinc     varchar(64),
  diaglesprinc       varchar(64),
  motifconssec       text,
  diaglessec         text,
  hdm                text,
  examenclinique     text,
  examenprescrit     text,
  traitementprescrit text,
  comment            text,
  typevisit          varchar(8) NOT NULL,
  infiltration       varchar(256),
  codageccam         varchar(16),
  montant_cheque     double(24,8),
  montant_espece     double(24,8),
  montant_carte      double(24,8),
  montant_tiers      double(24,8),
  banque             varchar(128),
  tms                timestamp
) ENGINE=innodb;
