<?php

/* themes/custom/aed_th/templates/layout/page--system--404.html.twig */
class __TwigTemplate_9f9d9a835765de9cc10d655b998680d94d0711c70ae8cde7adeaab89072c76f9 extends Twig_Template
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
        $tags = array("if" => 46);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('if'),
                array(),
                array()
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

        // line 46
        if ($this->getAttribute(($context["page"] ?? null), "header", array())) {
            // line 47
            echo "    <div class=\"region-wrapper region--header-wrapper\">
        <header class=\"region--header clearfix\">
            ";
            // line 49
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "header", array()), "html", null, true));
            echo "
        </header>
    </div>
";
        }
        // line 53
        echo "
<div class=\"region-wrapper region--content-wrapper\">
    <div class=\"region--content error-404 clearfix\">

        ";
        // line 57
        if ($this->getAttribute(($context["page"] ?? null), "left", array())) {
            // line 58
            echo "            <div class=\"region--left-wrapper clearfix\">
                ";
            // line 59
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "left", array()), "html", null, true));
            echo "
            </div>
        ";
        }
        // line 62
        echo "
        ";
        // line 63
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "content", array()), "html", null, true));
        echo "

        ";
        // line 65
        if ($this->getAttribute(($context["page"] ?? null), "right", array())) {
            // line 66
            echo "            <div class=\"region--right clearfix\">
                ";
            // line 67
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "right", array()), "html", null, true));
            echo "
            </div>
        ";
        }
        // line 70
        echo "
    </div>
</div>

";
        // line 74
        if ($this->getAttribute(($context["page"] ?? null), "social", array())) {
            // line 75
            echo "    <div class=\"region-wrapper region--social-wrapper\">
        <footer class=\"region--social clearfix\">
            ";
            // line 77
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "social", array()), "html", null, true));
            echo "
        </footer>
    </div>
";
        }
        // line 81
        echo "
";
        // line 82
        if ($this->getAttribute(($context["page"] ?? null), "drupal_social", array())) {
            // line 83
            echo "    <div class=\"region-wrapper region--drupal-social-wrapper\">
        <footer class=\"region--drupal-social clearfix\">
            ";
            // line 85
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "drupal_social", array()), "html", null, true));
            echo "
        </footer>
    </div>
";
        }
        // line 89
        echo "
";
        // line 90
        if ($this->getAttribute(($context["page"] ?? null), "footer", array())) {
            // line 91
            echo "    <div class=\"region-wrapper region--footer-wrapper\">
        <footer class=\"region--footer clearfix\">
            ";
            // line 93
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["page"] ?? null), "footer", array()), "html", null, true));
            echo "
        </footer>
    </div>
";
        }
        // line 97
        echo "

";
    }

    public function getTemplateName()
    {
        return "themes/custom/aed_th/templates/layout/page--system--404.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  143 => 97,  136 => 93,  132 => 91,  130 => 90,  127 => 89,  120 => 85,  116 => 83,  114 => 82,  111 => 81,  104 => 77,  100 => 75,  98 => 74,  92 => 70,  86 => 67,  83 => 66,  81 => 65,  76 => 63,  73 => 62,  67 => 59,  64 => 58,  62 => 57,  56 => 53,  49 => 49,  45 => 47,  43 => 46,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "themes/custom/aed_th/templates/layout/page--system--404.html.twig", "/var/www/AED/themes/custom/aed_th/templates/layout/page--system--404.html.twig");
    }
}
