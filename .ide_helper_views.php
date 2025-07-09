<?php
/**
 * This file is for IDE discovery only. It is never loaded by the application.
 *
 * HOW TO USE:
 * - Place `.ide_helper_views.php` in the project root.
 * - Make sure your IDE scans it:
 *      PhpStorm:
 *          Preferences → Directories → mark project root as "Sources".
 *      VS Code (Intelephense):
 *          "intelephense.environment.includePaths": [
 *              "./.ide_helper_views.php"
 *          ]
 *
 * TARGETS (for clarity only):
 * - resources/Views/*
 * - app/Modules/<Module>/Views/*
 *
 * Declare global objects used inside view templates.
 */

/** 
 * Template view object. 
 * 
 * @var \Luminova\Template\View<\App\Application> $view 
 * @var \Luminova\Template\View<\App\Application> $this
 */
$view = new \Luminova\Template\View(new \App\Application());
$this = $view;

/**
 * `$self` refers to the scope engine wrapper inside templates.
 *
 * @var \Luminova\Template\Engines\Scope<\Luminova\Template\View,\App\Application> $self
 */
$self = new \Luminova\Template\Engines\Scope($view);

/** 
 * Some template engines use `$this` inside views. 
 * 
 * Assigning type helps IDE understand chained methods.
 *
 * @global \Luminova\Template\View<\App\Application> $this
 * @global \Luminova\Template\Engines\Scop $self
 * @example - Example 
 * ```php
 * // /resources/Views/index.php 
 * 
 * echo $this->app->propertyName;
 * echo $this->_href;
 * echo $this->_asset;
 * var_export($this->getOptions();)
 * ```
 */