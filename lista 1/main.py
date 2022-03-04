import random
import sys
import math

if len(sys.argv) < 2:
    print("no file path found")
    exit(0)

source_file = sys.argv[1]


class Entropy:
    length = 0
    symbol_dictionary = dict()
    conditional_symbol_dictionary = dict()

    def read_data(self, current, prev):
        self.symbol_dictionary[current] = self.symbol_dictionary[current] + 1 if current in self.symbol_dictionary else 1
        if current in self.conditional_symbol_dictionary:
            self.conditional_symbol_dictionary[current][prev] = self.conditional_symbol_dictionary[current][prev] + 1 if prev in self.conditional_symbol_dictionary[current] else 1
        else:
            self.conditional_symbol_dictionary[current] = {prev: 1}

    def analyze_entropy(self):
        entropy_value = 0
        cond_entropy_value = 0
        for key, value in self.symbol_dictionary.items():
            entropy_value += value * -math.log2(value / self.length)
            for conditional_value in self.conditional_symbol_dictionary[key].values():
                cond_entropy_value += -math.log2(conditional_value / self.length) * conditional_value

        return entropy_value / self.length, (cond_entropy_value - entropy_value) / self.length, entropy_value / self.length - (cond_entropy_value - entropy_value) / self.length

    def show(self):
        print(self.length)
        print(self.symbol_dictionary)
        print(self.conditional_symbol_dictionary)


try:
    with open(source_file, "rb") as reader:
        entropy = Entropy()
        prev_byte = 0b00000000
        byte = reader.read(1)
        while byte:
            byte = byte[0]
            entropy.length += 1
            entropy.read_data(byte, prev_byte)

            prev_byte = byte
            byte = reader.read(1)
        # entropy.show()
        print(entropy.analyze_entropy())
    reader.close()
except OSError:
    print("cannot open", source_file)



