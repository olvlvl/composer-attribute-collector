# Use case: Incompatible Signature

> [!NOTE]
> Fixed in [v2.1.0](https://github.com/olvlvl/composer-attribute-collector/releases/tag/v2.1.0)

Running the collector within Composer's realm is causing issues when the interfaces used by Composer
are incompatible with those used by the codebase, as reported by [#31][] and [#32][]. The issue was
fixed in [#35][], by running the collector as a command.

```
Fatal error: Declaration of Acme\MyLogger::emergency(Stringable|string $message, array $context = []): void must be compatible with Psr\Log\LoggerInterface::emergency($message, array $context = []) in /app/src/MyLogger.php on line 23
```

[#31]: https://github.com/olvlvl/composer-attribute-collector/issues/31
[#32]: https://github.com/olvlvl/composer-attribute-collector/issues/32
[#35]: https://github.com/olvlvl/composer-attribute-collector/pull/35
