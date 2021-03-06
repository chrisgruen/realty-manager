#
# Table structure for table 'tx_realtymanager_domain_model_objectimmo'
#

CREATE TABLE tx_realtymanager_domain_model_objectimmo (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    t3ver_oid int(11) DEFAULT '0' NOT NULL,
    t3ver_id int(11) DEFAULT '0' NOT NULL,
    t3ver_wsid int(11) DEFAULT '0' NOT NULL,
    t3ver_label varchar(30) DEFAULT '' NOT NULL,
    t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
    t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
    t3ver_count int(11) DEFAULT '0' NOT NULL,
    t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
    t3_origuid int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    object_number varchar(125) DEFAULT '' NOT NULL,
    object_type int(11) DEFAULT '0' NOT NULL,
    title text DEFAULT '' NOT NULL,
    emphasized tinyint(3) DEFAULT '0' NOT NULL,
    street varchar(255) DEFAULT '' NOT NULL,
    zip varchar(8) DEFAULT '' NOT NULL,
    city int(11) DEFAULT '0' NOT NULL,
    district int(11) DEFAULT '0' NOT NULL,
    country int(11) DEFAULT '0' NOT NULL,
    show_address tinyint(1) DEFAULT '0' NOT NULL,
    has_coordinates tinyint(1) DEFAULT '0' NOT NULL,
    coordinates_problem tinyint(1) DEFAULT '0' NOT NULL,
    longitude decimal(9,6) DEFAULT '0.000000' NOT NULL,
    latitude decimal(9,6) DEFAULT '0.000000' NOT NULL,
    distance_to_the_sea int(11) unsigned DEFAULT '0' NOT NULL,
    sea_view tinyint(1) DEFAULT '0' NOT NULL,
    number_of_rooms decimal(5,2) DEFAULT '0.00' NOT NULL,
    living_area varchar(32) DEFAULT '' NOT NULL,
    total_area varchar(32) DEFAULT '' NOT NULL,
    estate_size varchar(32) DEFAULT '' NOT NULL,
    rent_excluding_bills varchar(32) DEFAULT '' NOT NULL,
    rent_with_heating_costs decimal(10,2) DEFAULT '0.00' NOT NULL,
    extra_charges varchar(32) DEFAULT '' NOT NULL,
    heating_included tinyint(3) DEFAULT '0' NOT NULL,
    deposit varchar(32) DEFAULT '' NOT NULL,
    provision varchar(32) DEFAULT '' NOT NULL,
    usable_from varchar(32) DEFAULT '' NOT NULL,
    buying_price varchar(32) DEFAULT '' NOT NULL,
    hoa_fee varchar(32) DEFAULT '' NOT NULL,
    year_rent varchar(32) DEFAULT '' NOT NULL,
    rental_income_target decimal(10,2) DEFAULT '0.00' NOT NULL,
    status tinyint(3) DEFAULT '0' NOT NULL,
    apartment_type int(11) DEFAULT '0' NOT NULL,
    house_type int(11) DEFAULT '0' NOT NULL,
    floor int(11) DEFAULT '0' NOT NULL,
    floors int(11) DEFAULT '0' NOT NULL,
    bedrooms decimal(5,2) DEFAULT '0.00' NOT NULL,
    bathrooms decimal(5,2) DEFAULT '0.00' NOT NULL,
    heating_type varchar(32) DEFAULT '' NOT NULL,
    has_air_conditioning tinyint(3) DEFAULT '0' NOT NULL,
    garage_type int(11) DEFAULT '0' NOT NULL,
    garage_rent varchar(32) DEFAULT '' NOT NULL,
    garage_price varchar(32) DEFAULT '' NOT NULL,
    pets int(11) DEFAULT '0' NOT NULL,
    construction_year int(11) DEFAULT '0' NOT NULL,
    old_or_new_building tinyint(3) DEFAULT '0' NOT NULL,
    state int(11) DEFAULT '0' NOT NULL,
    balcony tinyint(3) DEFAULT '0' NOT NULL,
    garden tinyint(3) DEFAULT '0' NOT NULL,
    elevator tinyint(3) DEFAULT '0' NOT NULL,
    barrier_free tinyint(1) unsigned DEFAULT '0' NOT NULL,
    wheelchair_accessible tinyint(1) unsigned DEFAULT '0' NOT NULL,
    ramp tinyint(1) unsigned DEFAULT '0' NOT NULL,
    lifting_platform tinyint(1) unsigned DEFAULT '0' NOT NULL,
    suitable_for_the_elderly tinyint(1) unsigned DEFAULT '0' NOT NULL,
    assisted_living tinyint(1) unsigned DEFAULT '0' NOT NULL,
    fitted_kitchen tinyint(3) DEFAULT '0' NOT NULL,
    has_pool tinyint(3) DEFAULT '0' NOT NULL,
    has_community_pool tinyint(3) DEFAULT '0' NOT NULL,
    teaser text DEFAULT '' NOT NULL,
    description text DEFAULT '' NOT NULL,
    equipment text DEFAULT '' NOT NULL,
    layout varchar(255) DEFAULT '' NOT NULL,
    location text DEFAULT '' NOT NULL,
    misc text DEFAULT '' NOT NULL,
    details_page tinytext,
    attachments int(11) unsigned DEFAULT '0' NOT NULL,
    employer varchar(255) DEFAULT '' NOT NULL,
    openimmo_anid varchar(63) DEFAULT '' NOT NULL,
    openimmo_obid varchar(63) DEFAULT '' NOT NULL,
    utilization varchar(1023) DEFAULT '' NOT NULL,
    contact_data_source tinyint(1) DEFAULT '0' NOT NULL,
    contact_person varchar(32) DEFAULT '' NOT NULL,
    contact_person_first_name varchar(32) DEFAULT '' NOT NULL,
    contact_person_salutation varchar(32) DEFAULT '' NOT NULL,
    contact_email varchar(255) DEFAULT '' NOT NULL,
    phone_switchboard varchar(32) DEFAULT '' NOT NULL,
    phone_direct_extension varchar(32) DEFAULT '' NOT NULL,
    owner int(11) unsigned DEFAULT '0' NOT NULL,
    language varchar(32) DEFAULT '' NOT NULL,
    currency varchar(32) DEFAULT '' NOT NULL,
    advertised_date int(11) DEFAULT '0' NOT NULL,
    rent_per_square_meter decimal(8,2) DEFAULT '0.00' NOT NULL,
    shop_area decimal(8,2) DEFAULT '0.00' NOT NULL,
    sales_area decimal(8,2) DEFAULT '0.00' NOT NULL,
    total_usable_area decimal(8,2) DEFAULT '0.00' NOT NULL,
    storage_area decimal(8,2) DEFAULT '0.00' NOT NULL,
    office_space decimal(8,2) DEFAULT '0.00' NOT NULL,
    other_area decimal(8,2) DEFAULT '0.00' NOT NULL,
    window_bank decimal(8,2) DEFAULT '0.00' NOT NULL,
    site_occupancy_index decimal(5,2) DEFAULT '0.00' NOT NULL,
    floor_space_index decimal(5,2) DEFAULT '0.00' NOT NULL,
    parking_spaces int(11) unsigned DEFAULT '0' NOT NULL,
    furnishing_category tinyint(3) DEFAULT '0' NOT NULL,
    flooring varchar(255) DEFAULT '' NOT NULL,
    energy_certificate_type int(1) unsigned DEFAULT '0' NOT NULL,
    energy_certificate_valid_until varchar(32) DEFAULT '' NOT NULL,
    energy_consumption_characteristic varchar(255) DEFAULT '' NOT NULL,
    with_hot_water int(1) unsigned DEFAULT '0' NOT NULL,
    ultimate_energy_demand varchar(32) DEFAULT '' NOT NULL,
    primary_energy_carrier varchar(32) DEFAULT '' NOT NULL,
    electric_power_consumption_characteristic varchar(255) DEFAULT '' NOT NULL,
    heat_energy_consumption_characteristic varchar(255) DEFAULT '' NOT NULL,
    value_category varchar(32) DEFAULT '' NOT NULL,
    year_of_construction varchar(32) DEFAULT '' NOT NULL,
    energy_certificate_issue_date int(11) DEFAULT '0' NOT NULL,
    energy_certificate_year int(1) unsigned DEFAULT '0' NOT NULL,
    building_type int(1) unsigned DEFAULT '0' NOT NULL,
    energy_certificate_text varchar(1024) DEFAULT '' NOT NULL,
    heat_energy_requirement_value varchar(32) DEFAULT '' NOT NULL,
    heat_energy_requirement_class varchar(32) DEFAULT '' NOT NULL,
    total_energy_efficiency_value varchar(32) DEFAULT '' NOT NULL,
    total_energy_efficiency_class varchar(32) DEFAULT '' NOT NULL,
    furnished tinyint(1) unsigned DEFAULT '0' NOT NULL,
    cleaning tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tv_enabled tinyint(1) unsigned DEFAULT '0' NOT NULL,
    bicycleroom tinyint(1) unsigned DEFAULT '0' NOT NULL,
    washingroom tinyint(1) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record),
    KEY owner (owner),
    KEY city (city),
    KEY district (district)
);
#
# Table structure for table 'tx_realtymanager_domain_model_apartment_types'
#
CREATE TABLE tx_realtymanager_domain_model_apartment_types (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record)
);

#
# Table structure for table 'tx_realtymanager_domain_model_house_types'
#
CREATE TABLE tx_realtymanager_domain_model_house_types (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record)
);

#
# Table structure for table 'tx_realtymanager_domain_model_car_places'
#
CREATE TABLE tx_realtymanager_domain_model_car_places (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record)
);

#
# Table structure for table 'tx_realtymanager_domain_model_pets'
#
CREATE TABLE tx_realtymanager_domain_model_pets (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record)
);

#
# Table structure for table 'tx_realtymanager_domain_model_cities'
#
CREATE TABLE tx_realtymanager_domain_model_cities (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumblob,
    l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    districts int(11) DEFAULT '0' NOT NULL,
    save_folder int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record)
);

#
# Table structure for table 'tx_realtymanager_domain_model_districts'
#
CREATE TABLE tx_realtymanager_domain_model_districts (
    uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,
	l10n_source int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    city int(11) unsigned DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY dummy (is_dummy_record),
    KEY city (city)
);

CREATE TABLE tx_realtymanager_domain_model_employer (
	uid int(11) unsigned NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    pid_be_user int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	disable tinyint(4) DEFAULT '0' NOT NULL,
	company varchar(80) DEFAULT '' NOT NULL,	
	first_name varchar(50) DEFAULT '' NOT NULL,
	last_name varchar(50) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	zip varchar(10) DEFAULT '' NOT NULL,
	city varchar(50) DEFAULT '' NOT NULL,
	country varchar(50) DEFAULT '' NOT NULL,
	telephone varchar(20) DEFAULT '' NOT NULL,
	fax varchar(20) DEFAULT '' NOT NULL,
	email varchar(250) DEFAULT '' NOT NULL,
	www varchar(250) DEFAULT '' NOT NULL,
	image tinytext DEFAULT '' NOT NULL,
	TSconfig text DEFAULT '' NOT NULL,
	description text DEFAULT '' NOT NULL,
	openimmo_anid varchar(255) DEFAULT '' NOT NULL,
	import_folder varchar(255) DEFAULT '' NOT NULL,
	maximum_objects tinyint(4) DEFAULT '0' NOT NULL,
	PRIMARY KEY (`uid`),
	KEY parent (pid)
);

#
# Table structure for table 'fe_users'
#
#CREATE TABLE fe_users (
#    tx_realty_openimmo_anid varchar(64) DEFAULT '' NOT NULL,
#    tx_realty_maximum_objects tinyint(4) DEFAULT '0' NOT NULL
#);
