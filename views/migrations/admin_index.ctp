<div class="migrations index">
	<h1><?php __d('migrations', 'Migrations'); ?></h1>
	
	<p>
		<?php __d('migrations', 'This page contains a list of the current status of your application\'s migrations.'); ?>
		<?php __d('migrations', 'Click on each migration to return / migrate to this state.'); ?>
		<?php __d('migrations', 'Check all the wanted states and run all migrations at once by submitting the form.'); ?>
	</p>
	
	<?php if (empty($mapping)) : ?>
	<div class="error-message">
		<?php __d('migrations', 'No migrations found in this application'); ?>
	</div>
	<?php else :
		echo $this->Form->create('Migration', array('action' => 'run'));
			foreach($mapping as $plugin => $migrations) :
					$options = array($this->Html->link(__d('migrations', 'Reset', true), array('action' => 'run', $plugin, 0)));
					$value = '';
					foreach($migrations as $migration) :
						$options[$migration['version']] = $this->Html->link(
							$migration['name'],
							array('action' => 'run', $plugin, $migration['version']));
						if (!empty($migration['migrated'])) :
							$value = $migration['version'];
						endif;
					endforeach;

					echo $this->Form->input($plugin . '.version', array(
						'legend' => $plugin,
						'type' => 'radio',
						'value' => $value,
						'options' => $options));
			endforeach;
		echo $this->Form->end(__d('migrations', 'Run migrations', true));
	endif; ?>
</div>