<?php

class BashCompleteCommand extends CConsoleCommand
{
    private function getActions($commandName)
    {
        $app = Yii::app();

        $runner = new CConsoleCommandRunner();
        
        $runner->addCommands($app->getCommandPath());
        
        if(($command = $runner->createCommand($commandName))===null)
            ydie("Command not valid.\n\n");

		$command->init();
		$rawActions = $command->getOptionHelp();

        $actions = array();
        foreach($rawActions as $oneActionHelpString)
        {
            $components = explode(' ', $oneActionHelpString);
            $actionName = array_shift($components);

            $arguments = array();
            foreach($components as $oneFullArgument)
            {
                $isOptional = ($oneFullArgument{0} == '[');

                if($isOptional)
                    $oneFullArgument = substr($oneFullArgument, 1, -1);

                if(($equalsAt = strpos($oneFullArgument, '=')) !== false)
                    $parameterName = substr($oneFullArgument, 0, $equalsAt);

                else
                    $parameterName = $oneFullArgument;

                if($isOptional == false)
                    $parameterName = "$parameterName=";

                $arguments[] = $parameterName;
            }

            $actions[$actionName] = $arguments;
        }

        return $actions;
    }

    /**
     * We overload this by duplicating the existing Yii logic from the parent, 
     * while omitting any parameter handling. When the user is trying to auto-
     * complete parameters to Yii commands, Yii will want to process them as if 
     * they were parameters intended for -this- command.
     */
    function run($args)
    {
		list($action, $options, $args)=$this->resolveRequest($args);
		$methodName='action'.$action;
		if(!preg_match('/^\w+$/',$action) || !method_exists($this,$methodName))
			$this->usageError("Unknown action: ".$action);

		$method=new ReflectionMethod($this,$methodName);
		$params=array();

        if($this->beforeAction($action,$params))
		{
			$method->invokeArgs($this,$params);
			$this->afterAction($action,$params);
		}
    }

    function actionGetOptionsList()
    {
        $app = Yii::app();

        list(,,, $cur, $prev, $prevPrev) = $_SERVER['argv'];

        $runner = new CConsoleCommandRunner();
        $commandFiles = $runner->findCommands($app->getCommandPath());
        $commands = array_keys($commandFiles);

        $command = $action = $actions = null;
        $options = null;

        $strategy = null;

        // They want a list of commands.
        if($prev == 'yiic')
        {
            $strategy = 'wantcommands';
            $options = $commands;
        }

        // They've typed a command and want a list of actions for that command.
        else if($prevPrev == 'yiic')
        {
            $strategy = 'wantactions';
            $command = $prev;

            $options = array();
            if(in_array($prev, $commands))
            {
                // Display a list of actions for a command.

                if(($actions = $this->getActions($prev)) == false)
                    $options = array();

                else
                    $options = array_keys($actions);
            }
        }

        // They've typed a command and an action and want a list of parameters.
        else if(in_array($prevPrev, $commands))
        {
            $strategy = 'wantarguments';
            $command = $prevPrev;
            $action = $prev;

            if(($actions = $this->getActions($command)) !== false && isset($actions[$action]))
                $options = $actions[$action];
        }

        if($options === null)
            $options = array();
        
        print(implode(' ', $options));
  
        exit(0);
    }
}

