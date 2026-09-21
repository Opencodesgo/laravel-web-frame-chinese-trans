<?php
/**
 * Psy，Var Dumper，主持人意识
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\VarDumper;

/**
 * Presenter injects itself as a dependency to all objects which
 * implement PresenterAware.
 * 呈现器将自己作为依赖注入到所有对象中
 */
interface PresenterAware
{
    /**
     * Set a reference to the Presenter.
	 * 设置对演示者的引用
     *
     * @param Presenter $presenter
     */
    public function setPresenter(Presenter $presenter);
}
