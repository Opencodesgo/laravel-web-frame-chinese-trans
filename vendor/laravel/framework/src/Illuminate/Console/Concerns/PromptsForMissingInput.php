<?php
/**
 * Illuminate，控制台，问题，输入缺失提示
 */

namespace Illuminate\Console\Concerns;

use Illuminate\Contracts\Console\PromptsForMissingInput as PromptsForMissingInputContract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PromptsForMissingInput
{
    /**
     * Interact with the user before validating the input.
	 * 在验证输入之前与用户交互
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        if ($this instanceof PromptsForMissingInputContract) {
            $this->promptForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt the user for any missing arguments.
	 * 提示用户输入任何缺少的参数
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        $prompted = collect($this->getDefinition()->getArguments())
            ->filter(fn ($argument) => $argument->isRequired() && is_null($input->getArgument($argument->getName())))
            ->filter(fn ($argument) => $argument->getName() !== 'command')
            ->each(fn ($argument) => $input->setArgument(
                $argument->getName(),
                $this->askPersistently(
                    $this->promptForMissingArgumentsUsing()[$argument->getName()] ??
                    'What is '.lcfirst($argument->getDescription()).'?'
                )
            ))
            ->isNotEmpty();

        if ($prompted) {
            $this->afterPromptingForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
	 * 使用返回的问题提示缺少输入参数
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
	 * 在提示用户缺少参数后执行操作
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        //
    }

    /**
     * Whether the input contains any options that differ from the default values.
	 * 输入是否包含与默认值不同的选项
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @return bool
     */
    protected function didReceiveOptions(InputInterface $input)
    {
        return collect($this->getDefinition()->getOptions())
            ->reject(fn ($option) => $input->getOption($option->getName()) === $option->getDefault())
            ->isNotEmpty();
    }

    /**
     * Continue asking a question until an answer is provided.
	 * 继续问问题，直到得到答案。
     *
     * @param  string  $question
     * @return string
     */
    private function askPersistently($question)
    {
        $answer = null;

        while ($answer === null) {
            $answer = $this->components->ask($question);

            if ($answer === null) {
                $this->components->error('The answer is required.');
            }
        }

        return $answer;
    }
}
