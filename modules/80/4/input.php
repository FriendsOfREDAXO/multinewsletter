<div class="row">
	<div class="col-xs-4">
		Breite des Blocks auf Smartphones:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[20]"  class="form-control">
		<?php
		$values = [12=>"12 von 12 Spalten (ganze Breite)", 9=>"9 von 12 Spalten", 8=>"8 von 12 Spalten", 6=>"6 von 12 Spalten", 4=>"4 von 12 Spalten", 3=>"3 von 12 Spalten"];
		foreach($values as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[20]" == $key) {
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		Breite des Blocks auf Tablets:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[19]"  class="form-control">
		<?php
		$values = [12=>"12 von 12 Spalten (ganze Breite)", 9=>"9 von 12 Spalten", 8=>"8 von 12 Spalten", 6=>"6 von 12 Spalten", 4=>"4 von 12 Spalten", 3=>"3 von 12 Spalten"];
		foreach($values as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[19]" == $key) {
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		Breite des Blocks auf größeren Geräten:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[18]"  class="form-control">
		<?php
		$values = [12=>"12 von 12 Spalten (ganze Breite)", 9=>"9 von 12 Spalten", 8=>"8 von 12 Spalten", 6=>"6 von 12 Spalten", 4=>"4 von 12 Spalten", 3=>"3 von 12 Spalten"];
		foreach($values as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[18]" == $key) {
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
	<div class="col-xs-4">
		Auf größeren Bildschirmen zentrieren?
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[17]"  class="form-control">
		<?php
		$values_offset = [0=>"Nicht zentrieren.", 1=>"Zentrieren, wenn freie Breite von anderem Inhalt nicht genutzt wird"];
		foreach($values_offset as $key => $value) {
			echo '<option value="'. $key .'" ';
	
			if ("REX_VALUE[17]" == $key) {
				echo 'selected="selected" ';
			}
			echo '>'. $value .'</option>';
		}
		?>
		</select>
	</div>
</div>
<script>
	function offset_changer(value) {
		if (value === "12") {
			$("select[name='REX_INPUT_VALUE[17]']").parent().parent().slideUp();
		}
		else {
			$("select[name='REX_INPUT_VALUE[17]']").parent().parent().slideDown();
		}
	}

	// Hide on document load
	$(document).ready(function() {
		offset_changer($("select[name='REX_INPUT_VALUE[18]']").val());
	});

	// Hide on selection change
	$("select[name='REX_INPUT_VALUE[18]']").on('change', function(e) {
		offset_changer($(this).val());
	});
</script>
<div class="row">
	<div class="col-xs-12"><div style="border-top: 1px darkgrey solid; margin: 1em 0;"></div></div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
	<div class="col-xs-4">Welche Gruppen sollen vom Nutzer abonniert werden können?
		Wenn nur eine Gruppe markiert ist, wird dem Nutzer keine Auswahl angeboten.
	</div>
	<div class="col-xs-8">
	<?php
		// Gruppen
		$query = 'SELECT id, name  '.
				'FROM '. rex::getTablePrefix() .'375_group '.
				'ORDER BY name';
		$result = rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		$group_ids = [];
		for($i = 0; $i < $num_rows; $i++) {
			$group_ids[$result->getValue("id")] = $result->getValue("name");
			$result->next();
		}
		$select_feature = new rex_select(); 
		$select_feature->setName('REX_INPUT_VALUE[1][]'); 
		$select_feature->setMultiple(true); 
		$select_feature->setSize(10);
		$select_feature->setAttribute('class', 'form-control');

		// Daten
		foreach($group_ids as $group_ids => $name)  {
		  $select_feature->addOption($name, $group_ids); 
		}

		// Vorselektierung
		$features_selected = rex_var::toArray("REX_VALUE[1]");
		if(is_array($features_selected)) {
			foreach($features_selected as $group_id) {
				$select_feature->setSelected($group_id);
			}
		}

		echo $select_feature->show();
	?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[2]" value="true" <?php echo "REX_VALUE[2]" == 'true' ? ' checked="checked"' : ''; ?> class="form-control d2u_helper_toggle" />
	</div>
	<div class="col-xs-8">
		Im Formular auch nach dem Namen fragen?
	</div>
</div>
<div class="row">
	<div class="col-xs-12"><div style="border-top: 1px darkgrey solid; margin: 1em 0;"></div></div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
	<div class="col-xs-12">Texte, Bezeichnungen bzw. Übersetzugen werden im
		<a href="<?php print rex_url::backendPage('multinewsletter/config'); ?>">Multinewsletter Addon</a> verwaltet.
	</div>
</div>