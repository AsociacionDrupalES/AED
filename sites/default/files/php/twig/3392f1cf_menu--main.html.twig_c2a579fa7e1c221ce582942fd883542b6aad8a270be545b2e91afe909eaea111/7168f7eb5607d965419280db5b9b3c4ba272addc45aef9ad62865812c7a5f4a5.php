<?php

/* themes/custom/aed_th/templates/navigation/menu--main.html.twig */
class __TwigTemplate_65d04864b69ace00517a894fe6c6a7d550c60be0b58e3682f108d88904aadd89 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("import" => 21, "macro" => 37, "if" => 39, "for" => 47, "set" => 49);
        $filters = array("slice" => 27);
        $functions = array("link" => 57);

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('import', 'macro', 'if', 'for', 'set'),
                array('slice'),
                array('link')
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 21
        $context["menus"] = $this;
        // line 22
        echo "
";
        // line 27
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($context["menus"]->getmenu_links(twig_slice($this->env, ($context["items"] ?? null), 0, 2), ($context["outside_attributes_menu"] ?? null), 0)));
        echo "

<a href=\"#\" class=\"menu-open\">Menu</a>

<div class=\"hidden-menu\">
    <a href=\"#\" class=\"menu-close\">X</a>
    ";
        // line 33
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($context["menus"]->getmenu_links(twig_slice($this->env, ($context["items"] ?? null), 2, null), ($context["attributes"] ?? null), 0)));
        echo "
</div>


";
    }

    // line 37
    public function getmenu_links($__items__ = null, $__attributes__ = null, $__menu_level__ = null, ...$__varargs__)
    {
        $context = $this->env->mergeGlobals(array(
            "items" => $__items__,
            "attributes" => $__attributes__,
            "menu_level" => $__menu_level__,
            "varargs" => $__varargs__,
        ));

        $blocks = array();

        ob_start();
        try {
            // line 38
            echo "  ";
            $context["menus"] = $this;
            // line 39
            echo "  ";
            if (($context["items"] ?? null)) {
                // line 40
                echo "    ";
                if ((($context["menu_level"] ?? null) == 0)) {
                    // line 41
                    echo "
<ul";
                    // line 42
                    echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["attributes"] ?? null), "html", null, true));
                    echo ">
    ";
                } else {
                    // line 44
                    echo "    <ul>
        ";
                }
                // line 46
                echo "
        ";
                // line 47
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["items"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 48
                    echo "
            ";
                    // line 49
                    $context["classes"] = array(0 => "menu-item", 1 => (($this->getAttribute(                    // line 51
$context["item"], "is_expanded", array())) ? ("menu-item--expanded") : ("")), 2 => (($this->getAttribute(                    // line 52
$context["item"], "is_collapsed", array())) ? ("menu-item--collapsed") : ("")), 3 => (($this->getAttribute(                    // line 53
$context["item"], "in_active_trail", array())) ? ("menu-item--active-trail") : ("")));
                    // line 55
                    echo "
            <li";
                    // line 56
                    echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute($this->getAttribute($context["item"], "attributes", array()), "addClass", array(0 => ($context["classes"] ?? null)), "method"), "html", null, true));
                    echo ">
                ";
                    // line 57
                    echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\Core\Template\TwigExtension')->getLink($this->getAttribute($context["item"], "title", array()), $this->getAttribute($context["item"], "url", array())), "html", null, true));
                    echo "
                ";
                    // line 58
                    if ($this->getAttribute($context["item"], "below", array())) {
                        // line 59
                        echo "                    ";
                        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($context["menus"]->getmenu_links($this->getAttribute($context["item"], "below", array()), ($context["attributes"] ?? null), (($context["menu_level"] ?? null) + 1))));
                        echo "
                ";
                    }
                    // line 61
                    echo "            </li>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 63
                echo "    </ul>
    ";
            }
            // line 65
            echo "    ";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "themes/custom/aed_th/templates/navigation/menu--main.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  146 => 65,  142 => 63,  135 => 61,  129 => 59,  127 => 58,  123 => 57,  119 => 56,  116 => 55,  114 => 53,  113 => 52,  112 => 51,  111 => 49,  108 => 48,  104 => 47,  101 => 46,  97 => 44,  92 => 42,  89 => 41,  86 => 40,  83 => 39,  80 => 38,  66 => 37,  57 => 33,  48 => 27,  45 => 22,  43 => 21,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "themes/custom/aed_th/templates/navigation/menu--main.html.twig", "/var/www/AED/themes/custom/aed_th/templates/navigation/menu--main.html.twig");
    }
}
